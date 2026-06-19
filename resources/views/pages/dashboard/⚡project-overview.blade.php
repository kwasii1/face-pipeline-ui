<?php

use App\Models\Face;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    public Project $project;

    public string $deleteConfirmName = '';

    public $totalPhotos;
    public $totalFaces;
    public $taggedPeople;
    public $needsReview;
    public $untaggedFaces;

    public function mount(Project $project): void
    {
        $this->totalPhotos = $project->photos()->count();
        $this->totalFaces = Face::whereHas('photo', fn ($q) => $q->where('project_id', $project->id))->count();
        $this->taggedPeople = $project->people()->count();
        $this->needsReview = Face::whereHas('photo', fn ($q) => $q->where('project_id', $project->id))
            ->whereNull('person_id')
            ->count();

        $this->untaggedFaces = Face::with('photo')
            ->whereHas('photo', fn ($q) => $q->where('project_id', $project->id))
            ->whereNull('person_id')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function deleteProject(): void
    {
        if ($this->deleteConfirmName !== $this->project->name) {
            return;
        }

        $name = $this->project->name;
        $this->project->delete();

        $this->dispatch('toast', message: "Project \"{$name}\" deleted.", type: 'success');

        $this->redirectRoute('projects', navigate: true);
    }

    public function cancelDelete(): void
    {
        $this->deleteConfirmName = '';
    }
};
?>


<div class="p-6 max-w-4xl mx-auto"
    x-data="{ showDeleteModal: false }"
>
    <div class="flex items-start justify-between mb-1">
        <h1 class="font-mono text-xl font-bold text-text-pri">{{ $project->name }}</h1>
        <button
            x-on:click="showDeleteModal = true"
            class="font-mono text-xs text-text-faint hover:text-accent transition-colors uppercase tracking-wider shrink-0 mt-1"
        >
            Delete
        </button>
    </div>
    <x-scanline-rule class="w-32 mb-8" />

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-10">
        <x-stat-card label="Total Photos" :value="$totalPhotos" />
        <x-stat-card label="Faces Found" :value="$totalFaces" />
        <x-stat-card label="Tagged People" :value="$taggedPeople" />
        <x-stat-card label="Needs Review" :value="$needsReview" :href="route('project.faces', $project)" />
    </div>

    @if ($untaggedFaces->isNotEmpty())
        <div class="mb-4">
            <h2 class="font-mono text-sm font-medium text-text-pri mb-1">Needs your attention</h2>
            <p class="font-sans text-xs text-text-muted mb-4">{{ $needsReview }} {{ $needsReview === 1 ? 'face needs' : 'faces need' }} review</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($untaggedFaces as $face)
                <a href="{{ route('project.faces', $project) }}" wire:navigate>
                    <x-face-card :face="$face" :clickable="false" />
                </a>
            @endforeach
        </div>
    @else
        <x-empty-state title="All caught up" description="Every face has been tagged. Nice work." />
    @endif

    <div
        x-show="showDeleteModal"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;"
        x-cloak
    >
        <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="showDeleteModal = false; $wire.cancelDelete()"></div>

        <div class="relative z-10 w-full max-w-md bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
            <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                <h2 class="font-mono text-sm font-medium text-text-pri">Delete Project</h2>
                <button x-on:click="showDeleteModal = false; $wire.cancelDelete()" class="text-text-muted hover:text-text-pri transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-5 space-y-4">
                <p class="font-sans text-sm text-text-muted leading-relaxed">
                    This will permanently delete <span class="text-text-pri font-medium">{{ $project->name }}</span> and all its photos, faces, people, and batches.
                </p>

                <div>
                    <label class="block font-mono text-xs text-text-muted mb-1.5">
                        Type <span class="text-text-pri">{{ $project->name }}</span> to confirm
                    </label>
                    <input
                        type="text"
                        wire:model.live="deleteConfirmName"
                        wire:keydown.enter="deleteProject"
                        class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light"
                        placeholder="{{ $project->name }}"
                    />
                </div>
            </div>

            <div class="px-5 py-4 border-t border-border flex justify-end gap-3">
                <button
                    x-on:click="showDeleteModal = false; $wire.cancelDelete()"
                    class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                >
                    Cancel
                </button>
                <button
                    wire:click="deleteProject"
                    @class([
                        'px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase rounded transition-opacity',
                        'hover:opacity-90' => $deleteConfirmName === $project->name,
                        'opacity-30 cursor-not-allowed' => $deleteConfirmName !== $project->name,
                    ])
                    @disabled($deleteConfirmName !== $project->name)
                >
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
