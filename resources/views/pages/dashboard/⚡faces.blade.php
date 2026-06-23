<?php

use App\Models\Face;
use App\Models\Person;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    use WithPagination;

    public Project $project;

    public ?Face $selectedFace = null;

    public string $tagName = '';

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function untaggedFaces()
    {
        return Face::with('person', 'photo')
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereNull('person_id')
            ->orderBy('blur_score', 'desc')
            ->paginate(24, pageName: 'untagged');
    }

    public function taggedFaces()
    {
        return Face::with('person', 'photo')
            ->whereHas('photo', fn ($q) => $q->where('project_id', $this->project->id))
            ->whereNotNull('person_id')
            ->orderBy('blur_score', 'desc')
            ->paginate(24, pageName: 'tagged');
    }

    public function selectFace(string $id): void
    {
        $this->selectedFace = Face::with('person', 'photo')->find($id);
        $this->tagName = '';
    }

    public function saveTag(): void
    {
        if (! $this->selectedFace || empty(trim($this->tagName))) {
            return;
        }

        $name = trim($this->tagName);

        $person = Person::firstOrCreate([
            'project_id' => $this->project->id,
            'name' => $name,
        ]);

        if ($this->selectedFace->cluster_id) {
            $count = Face::where('cluster_id', $this->selectedFace->cluster_id)
                ->update([
                    'person_id' => $person->id,
                ]);
        } else {
            $this->selectedFace->update([
                'person_id' => $person->id,
            ]);

            $count = 1;
        }

        $this->resetPage('untagged');
        $this->selectedFace = null;
        $this->tagName = '';

        $this->dispatch(
            'toast',
            message: $count > 1
                ? "{$count} faces tagged as {$name}."
                : "Face tagged as {$name}.",
            type: 'success'
        );
    }
};
?>


<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Faces</h1>
    <x-scanline-rule class="w-24 mb-8" />

    @php $untaggedFaces = $this->untaggedFaces(); @endphp

    @if ($untaggedFaces->isNotEmpty())
        <h2 class="font-mono text-sm font-medium text-text-pri mb-4">
            Needs review
            <span class="ml-2 text-xs text-text-muted">{{ $untaggedFaces->total() }}</span>
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-4">
            @foreach ($untaggedFaces as $face)
                <x-face-card :face="$face" />
            @endforeach
        </div>

        {{ $untaggedFaces->links('components.pagination') }}
    @endif

    @php $taggedFaces = $this->taggedFaces(); @endphp

    @if ($taggedFaces->isNotEmpty())
        <h2 class="font-mono text-sm font-medium text-text-pri mt-10 mb-4">
            Tagged
            <span class="ml-2 text-xs text-text-muted">{{ $taggedFaces->total() }}</span>
        </h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-4">
            @foreach ($taggedFaces as $face)
                <x-face-card :face="$face" />
            @endforeach
        </div>

        {{ $taggedFaces->links('components.pagination') }}
    @endif

    @if ($untaggedFaces->isEmpty() && $taggedFaces->isEmpty())
        <x-empty-state title="No faces found" description="Faces will appear here after photos are processed." />
    @endif

    @if ($selectedFace)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.set('selectedFace', null) })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-sm bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h2 class="font-mono text-sm font-medium text-text-pri">
                        {{ $selectedFace->person_id ? 'Edit tag' : 'Review face' }}
                    </h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <div class="aspect-square bg-surface-alt rounded overflow-hidden">
                        <img
                            src="{{ Storage::disk('shared')->url($selectedFace->crop_path) }}"
                            alt="Face crop"
                            class="w-full h-full object-cover"
                        />
                    </div>

                    @if ($selectedFace->cluster_id && ! $selectedFace->person_id)
                        <p class="font-mono text-xs text-text-faint">
                            Part of cluster {{ $selectedFace->cluster_id }} &mdash; tagging this face will tag all faces in this cluster.
                        </p>
                    @endif

                    <div>
                        <label class="block font-mono text-xs text-text-muted mb-1.5">Name</label>
                        <input
                            type="text"
                            wire:model="tagName"
                            wire:keydown.enter="saveTag"
                            class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light"
                            placeholder="Enter person name..."
                        />
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
                        wire:click="saveTag"
                        class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase hover:opacity-90 transition-opacity rounded"
                    >
                        Save name
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
