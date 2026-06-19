<?php

use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    public string $newName = '';

    public string $newDescription = '';

    public ?Project $deletingProject = null;

    public string $deleteConfirmName = '';

    public function createProject(): void
    {
        $this->validate([
            'newName' => ['required', 'string', 'max:255'],
            'newDescription' => ['nullable', 'string'],
        ]);

        $project = Project::create([
            'name' => $this->newName,
            'description' => $this->newDescription ?: null,
        ]);

        $this->dispatch('toast', message: 'Project created.', type: 'success');

        $this->redirectRoute('project.overview', $project, navigate: true);
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingProject = Project::find($id);
        $this->deleteConfirmName = '';
    }

    public function deleteProject(): void
    {
        if (! $this->deletingProject) {
            return;
        }

        if ($this->deleteConfirmName !== $this->deletingProject->name) {
            return;
        }

        $name = $this->deletingProject->name;
        $this->deletingProject->delete();

        $this->dispatch('toast', message: "Project \"{$name}\" deleted.", type: 'success');

        $this->redirectRoute('projects', navigate: true);
    }

    public function cancelDelete(): void
    {
        $this->deletingProject = null;
        $this->deleteConfirmName = '';
    }
};
?>

@php
    $projects = \App\Models\Project::withCount(['photos', 'faces'])->latest()->get();
@endphp

<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Projects</h1>
    <x-scanline-rule class="w-24 mb-8" />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($projects as $project)
            <x-project-card :project="$project" />
        @endforeach

        <x-project-card :is-new="true" />
    </div>

    <x-modal id="create-project" title="New Project">
        <div class="space-y-4">
            <div>
                <label class="block font-mono text-xs text-text-muted mb-1.5">Name</label>
                <input
                    type="text"
                    wire:model="newName"
                    class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light"
                    placeholder="Project name"
                />
            </div>
            <div>
                <label class="block font-mono text-xs text-text-muted mb-1.5">Description (optional)</label>
                <textarea
                    wire:model="newDescription"
                    rows="3"
                    class="w-full bg-bg border border-border rounded px-3 py-2 font-sans text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light resize-none"
                    placeholder="Brief description..."
                ></textarea>
            </div>
        </div>

        @slot('footer')
            <div class="flex justify-end gap-3">
                <button
                    x-on:click="$dispatch('close-modal')"
                    class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                >
                    Cancel
                </button>
                <button
                    wire:click="createProject"
                    class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase hover:opacity-90 transition-opacity rounded"
                >
                    Create
                </button>
            </div>
        @endslot
    </x-modal>

    @if ($deletingProject)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition.opacity.duration.200ms
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="display: none;"
            x-cloak
            x-init="$watch('open', v => { if(!v) $wire.cancelDelete() })"
        >
            <div class="absolute inset-0 bg-bg/80 backdrop-blur-sm" x-on:click="open = false"></div>

            <div class="relative z-10 w-full max-w-md bg-surface border border-border rounded-lg shadow-2xl" x-on:click.stop="">
                <div class="flex items-center justify-between px-5 py-4 border-b border-border">
                    <h2 class="font-mono text-sm font-medium text-text-pri">Delete Project</h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <p class="font-sans text-sm text-text-muted leading-relaxed">
                        This will permanently delete <span class="text-text-pri font-medium">{{ $deletingProject->name }}</span> and all its photos, faces, people, and batches.
                    </p>

                    <div>
                        <label class="block font-mono text-xs text-text-muted mb-1.5">
                            Type <span class="text-text-pri">{{ $deletingProject->name }}</span> to confirm
                        </label>
                        <input
                            type="text"
                            wire:model="deleteConfirmName"
                            wire:keydown.enter="deleteProject"
                            class="w-full bg-bg border border-border rounded px-3 py-2 font-mono text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light"
                            placeholder="{{ $deletingProject->name }}"
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
                        wire:click="deleteProject"
                        @class([
                            'px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase rounded transition-opacity',
                            'hover:opacity-90' => $deleteConfirmName === $deletingProject->name,
                            'opacity-30 cursor-not-allowed' => $deleteConfirmName !== $deletingProject->name,
                        ])
                        @disabled($deleteConfirmName !== $deletingProject->name)
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
