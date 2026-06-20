<?php

use App\Models\Person;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    public Project $project;
    public $people;
    public ?Person $deletingPerson = null;

    public function mount(Project $project): void
    {
        $this->people = $project->people()->withCount('faces')->orderBy('name')->get();
    }

    public function confirmDelete(string $id): void
    {
        $this->deletingPerson = Person::withCount('faces')->find($id);
    }

    public function deletePerson(): void
    {
        if (! $this->deletingPerson) {
            return;
        }

        $name = $this->deletingPerson->name;
        $this->deletingPerson->delete();

        $this->people = $this->project->people()->withCount('faces')->orderBy('name')->get();
        $this->deletingPerson = null;

        $this->dispatch('toast', message: "Person \"{$name}\" deleted.", type: 'success');
    }

    public function cancelDelete(): void
    {
        $this->deletingPerson = null;
    }
};
?>


<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">People</h1>
    <x-scanline-rule class="w-24 mb-8" />

    @if ($people->isNotEmpty())
        <div class="space-y-1">
            @foreach ($people as $person)
                <div class="bg-surface border border-border rounded-lg px-4 py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="w-8 h-8 rounded-full bg-surface-alt flex items-center justify-center text-xs font-bold text-text-muted shrink-0">
                            {{ strtoupper(substr($person->name, 0, 1)) }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-mono text-sm text-text-pri truncate">{{ $person->name }}</p>
                            <p class="font-mono text-xs text-text-muted">{{ $person->faces_count }} {{ $person->faces_count === 1 ? 'face' : 'faces' }}</p>
                        </div>
                    </div>
                    <button
                        wire:click="confirmDelete('{{ $person->id }}')"
                        class="shrink-0 text-text-muted hover:text-red-400 transition-colors p-1"
                        title="Delete person"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @else
        <x-empty-state title="No people yet" description="Tag faces to create people." />
    @endif

    @if ($deletingPerson)
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
                    <h2 class="font-mono text-sm font-medium text-text-pri">Delete Person</h2>
                    <button x-on:click="open = false" class="text-text-muted hover:text-text-pri transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 space-y-4">
                    <p class="font-sans text-sm text-text-muted leading-relaxed">
                        Delete <span class="text-text-pri font-medium">{{ $deletingPerson->name }}</span>?
                        This will un-tag {{ $deletingPerson->faces_count }} {{ $deletingPerson->faces_count === 1 ? 'face' : 'faces' }}.
                    </p>
                </div>

                <div class="px-5 py-4 border-t border-border flex justify-end gap-3">
                    <button
                        x-on:click="open = false"
                        class="font-mono text-xs text-text-muted hover:text-text-pri transition-colors uppercase tracking-wider"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="deletePerson"
                        class="px-4 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase hover:opacity-90 transition-opacity rounded"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
