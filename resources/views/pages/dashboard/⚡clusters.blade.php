<?php

use App\Models\Face;
use App\Models\Project;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    use WithPagination;

    public Project $project;

    public ?string $selectedCluster = null;

    public ?string $reassigningFaceId = null;

    public string $reassignTargetCluster = '';

    public array $mergeSelection = [];

    public string $mergeTarget = '';

    public bool $addingFace = false;

    public ?string $addFaceId = null;

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    #[Computed()]
    public function clusters()
    {
        $clusters = Face::whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereNotNull('cluster_id')
            ->selectRaw("
                cluster_id,
                COUNT(*) as face_count,
                COUNT(person_id) as tagged_count,
                (SELECT f2.id FROM faces f2
                 WHERE f2.cluster_id = faces.cluster_id
                   AND f2.cluster_id IS NOT NULL
                 ORDER BY f2.blur_score DESC NULLS LAST
                 LIMIT 1) as cover_face_id
            ")
            ->groupBy('cluster_id')
            ->orderByRaw('COUNT(person_id) DESC')
            ->get();

        $coverFaces = Face::whereIn('id', $clusters->pluck('cover_face_id')->filter())
            ->with('person')
            ->get()
            ->keyBy('id');

        return $clusters->map(function ($cluster) use ($coverFaces) {
            $cluster->coverFace = $coverFaces->get($cluster->cover_face_id);
            $cluster->untagged_count = $cluster->face_count - $cluster->tagged_count;

            return $cluster;
        });
    }

    public function clusterFaces()
    {
        if (! $this->selectedCluster) {
            return collect();
        }

        return Face::with('person', 'photo')
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->where('cluster_id', $this->selectedCluster)
            ->orderBy('blur_score', 'desc')
            ->paginate(24, pageName: 'faces');
    }

    public function unclusteredFaces()
    {
        return Face::with('person', 'photo')
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereNull('cluster_id')
            ->orderBy('blur_score', 'desc')
            ->paginate(24, pageName: 'unclustered');
    }

    public function selectCluster(string $clusterId): void
    {
        $this->selectedCluster = $clusterId;
        $this->reassigningFaceId = null;
        $this->reassignTargetCluster = '';
        $this->resetPage('faces');
    }

    public function closeCluster(): void
    {
        $this->selectedCluster = null;
        $this->reassigningFaceId = null;
        $this->reassignTargetCluster = '';
    }

    public function removeFromCluster(string $faceId): void
    {
        $face = Face::find($faceId);

        if ($face) {
            $face->update([
                'cluster_id' => null,
                'person_id' => null,
            ]);
        }

        $clusterExists = Face::where('cluster_id', $this->selectedCluster)
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->exists();

        if (! $clusterExists) {
            $this->selectedCluster = null;
        }

        $this->dispatch('toast', message: 'Face removed from cluster.', type: 'success');
    }

    public function startReassign(string $faceId): void
    {
        $this->reassigningFaceId = $faceId;
        $this->reassignTargetCluster = '';
    }

    public function cancelReassign(): void
    {
        $this->reassigningFaceId = null;
        $this->reassignTargetCluster = '';
    }

    public function moveToCluster(): void
    {
        if (! $this->reassigningFaceId || empty($this->reassignTargetCluster)) {
            return;
        }

        $face = Face::find($this->reassigningFaceId);

        if ($face) {
            $face->update([
                'cluster_id' => $this->reassignTargetCluster,
            ]);
        }

        $this->dispatch('toast', message: 'Face moved to '.$this->reassignTargetCluster.'.', type: 'success');
        $this->reassigningFaceId = null;
        $this->reassignTargetCluster = '';
    }

    public function toggleMerge(string $clusterId): void
    {
        if (in_array($clusterId, $this->mergeSelection)) {
            $this->mergeSelection = array_values(array_diff($this->mergeSelection, [$clusterId]));
        } else {
            $this->mergeSelection[] = $clusterId;
        }
    }

    public function cancelMerge(): void
    {
        $this->mergeSelection = [];
        $this->mergeTarget = '';
    }

    public function mergeClusters(): void
    {
        if (empty($this->mergeTarget) || count($this->mergeSelection) < 2) {
            return;
        }

        if (! in_array($this->mergeTarget, $this->mergeSelection)) {
            return;
        }

        $sources = array_values(array_diff($this->mergeSelection, [$this->mergeTarget]));

        Face::whereIn('cluster_id', $sources)
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->update(['cluster_id' => $this->mergeTarget]);

        $count = count($sources);

        $this->dispatch('toast', message: $count.' '.Str::plural('cluster', $count).' merged into '.$this->mergeTarget.'.', type: 'success');
        $this->mergeSelection = [];
        $this->mergeTarget = '';
    }

    public function otherClusterIds(): array
    {
        return Face::whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereNotNull('cluster_id')
            ->where('cluster_id', '!=', $this->selectedCluster)
            ->distinct()
            ->pluck('cluster_id')
            ->sort()
            ->values()
            ->toArray();
    }

    public function startAddFace(): void
    {
        $this->addingFace = true;
        $this->addFaceId = null;
        $this->resetPage('unclustered');
    }

    public function cancelAddFace(): void
    {
        $this->addingFace = false;
        $this->addFaceId = null;
    }

    public function selectAddFace(string $faceId): void
    {
        $this->addFaceId = $faceId;
    }

    public function addToCluster(): void
    {
        if (! $this->selectedCluster || ! $this->addFaceId) {
            return;
        }

        $face = Face::find($this->addFaceId);

        if ($face) {
            $face->update(['cluster_id' => $this->selectedCluster]);
        }

        $this->dispatch('toast', message: 'Face added to cluster.', type: 'success');
        $this->addFaceId = null;
    }
};
?>


<div class="p-6 max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-1">
        <h1 class="font-mono text-xl font-bold text-text-pri">Clusters</h1>
        <div class="flex items-center gap-2">
            @if (count($mergeSelection) >= 2)
                <button
                    x-data
                    x-on:click="$dispatch('open-merge-modal')"
                    class="px-3 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase hover:opacity-90 transition-opacity rounded"
                >
                    Merge {{ count($mergeSelection) }} clusters
                </button>
            @endif
            @if (count($mergeSelection) > 0)
                <button
                    wire:click="cancelMerge"
                    class="px-3 py-1.5 font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                >
                    Cancel merge
                </button>
            @endif
        </div>
    </div>
    <x-scanline-rule class="w-24 mb-8" />


    @if ($this->clusters->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach ($this->clusters as $cluster)
                <x-cluster-card
                    :cluster="$cluster"
                    :mergeSelection="$mergeSelection"
                    :wireClick="empty($mergeSelection) ? 'selectCluster(\''.$cluster->cluster_id.'\')' : null"
                    :wireClickMerge="'toggleMerge(\''.$cluster->cluster_id.'\')'"
                />
            @endforeach
        </div>
    @else
        <x-empty-state title="No clusters yet" description="Clusters will appear here after faces are clustered." />
    @endif

    @if ($selectedCluster)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.closeCluster() })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-3xl max-h-[85vh] bg-surface border border-border rounded-lg shadow-2xl flex flex-col" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
                    <div>
                        <h2 class="font-mono text-sm font-medium text-text-pri">
                            {{ $selectedCluster }}
                        </h2>
                        <p class="font-mono text-[10px] text-text-muted mt-0.5">
                            {{ $this->clusterFaces()->total() }} {{ $this->clusterFaces()->total() === 1 ? 'face' : 'faces' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="startAddFace"
                            class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                        >
                            Add face
                        </button>
                        <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-y-auto p-5">
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                        @foreach ($this->clusterFaces() as $face)
                            <div class="group bg-surface-alt rounded-lg overflow-hidden border border-border">
                                <div class="aspect-square bg-surface-alt flex items-center justify-center">
                                    <img
                                        src="{{ Storage::disk('shared')->url($face->crop_path) }}"
                                        alt="Face crop"
                                        class="w-full h-full object-cover"
                                        loading="lazy"
                                    />
                                </div>

                                <div class="p-1.5">
                                    <p class="font-mono text-[10px] text-text-pri truncate">
                                        {{ $face->person?->name ?? 'Unnamed' }}
                                    </p>
                                </div>

                                <div class="flex border-t border-border">
                                    <button
                                        wire:click="removeFromCluster('{{ $face->id }}')"
                                        class="flex-1 py-1.5 font-mono text-[10px] text-text-faint hover:text-red-400 hover:bg-red-400/10 transition-colors uppercase tracking-wider"
                                        title="Remove from cluster"
                                    >
                                        Remove
                                    </button>
                                    <button
                                        wire:click="startReassign('{{ $face->id }}')"
                                        class="flex-1 py-1.5 font-mono text-[10px] text-text-faint hover:text-accent hover:bg-accent/10 transition-colors uppercase tracking-wider border-l border-border"
                                        title="Move to another cluster"
                                    >
                                        Move
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{ $this->clusterFaces()->links('components.pagination') }}
                </div>
            </div>
        </div>
    @endif

    @if ($reassigningFaceId)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[60] flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.cancelReassign() })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-xs bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h2 class="font-mono text-sm font-medium text-text-pri">Move face</h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="block font-mono text-xs text-text-muted mb-1.5">Target cluster</label>
                        <select
                            wire:model="reassignTargetCluster"
                            class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri focus:outline-none focus:border-border-light"
                        >
                            <option value="">Select cluster...</option>
                            @foreach ($this->otherClusterIds() as $cid)
                                <option value="{{ $cid }}">{{ $cid }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-border flex justify-end gap-3">
                    <button
                        x-on:click="open = false"
                        class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="moveToCluster"
                        @disabled(empty($reassignTargetCluster))
                        class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase rounded transition-opacity hover:opacity-90 disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                        Move
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (count($mergeSelection) >= 2)
        <div
            x-data="{ open: false }"
            x-show="open"
            x-transition.opacity.duration.200ms
            x-on:open-merge-modal.window="open = true"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.cancelMerge() })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-sm bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h2 class="font-mono text-sm font-medium text-text-pri">Merge clusters</h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <p class="font-sans text-xs text-text-muted leading-relaxed">
                        All faces from the other {{ count($mergeSelection) - 1 }} selected {{ Str::plural('cluster', count($mergeSelection) - 1) }} will be moved into the target cluster.
                    </p>

                    <div>
                        <label class="block font-mono text-xs text-text-muted mb-1.5">Merge into</label>
                        <select
                            wire:model.live="mergeTarget"
                            class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri focus:outline-none focus:border-border-light"
                        >
                            <option value="">Select target...</option>
                            @foreach ($mergeSelection as $cid)
                                <option value="{{ $cid }}">{{ $cid }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-border flex justify-end gap-3">
                    <button
                        x-on:click="open = false"
                        class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="mergeClusters"
                        @disabled(empty($mergeTarget))
                        class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase rounded transition-opacity hover:opacity-90 disabled:opacity-30 disabled:cursor-not-allowed"
                    >
                        Merge
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($addingFace && $selectedCluster)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.cancelAddFace() })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-2xl max-h-[85vh] bg-surface border border-border rounded-lg shadow-2xl flex flex-col" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
                    <h2 class="font-mono text-sm font-medium text-text-pri">
                        Add face to {{ $selectedCluster }}
                    </h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto p-5">
                    <x-face-picker
                        :faces="$this->unclusteredFaces()"
                        :selectedId="$addFaceId"
                        action="selectAddFace"
                        emptyText="No unclustered faces available."
                    />
                </div>

                <div class="px-5 py-4 border-t border-border shrink-0 flex justify-end gap-3">
                    <button
                        x-on:click="open = false"
                        class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                    >
                        Close
                    </button>
                    @if ($addFaceId)
                        <button
                            wire:click="addToCluster"
                            class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase rounded hover:opacity-90 transition-opacity"
                        >
                            Add selected
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
