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

        $this->redirectRoute('project.overview', $project, navigate: true);
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
</div>
