<?php

use App\Models\Photo;
use App\Models\Project;
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

    public string $search = '';

    public array $selectedPersonIds = [];

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    #[Computed()]
    public function people()
    {
        $query = $this->project->people();

        if (! empty($this->search)) {
            $query->where('name', 'ilike', '%'.$this->search.'%');
        }

        return $query->orderBy('name')->get();
    }

    public function results()
    {
        if (empty($this->selectedPersonIds)) {
            return collect();
        }

        $query = Photo::where('project_id', $this->project->id);

        foreach ($this->selectedPersonIds as $personId) {
            $query->whereHas('faces', fn ($q) => $q->where('person_id', $personId));
        }

        return $query->with('faces.person')->latest()->paginate(12);
    }

    public function togglePerson(string $id): void
    {
        if (in_array($id, $this->selectedPersonIds)) {
            $this->selectedPersonIds = array_values(array_filter($this->selectedPersonIds, fn ($v) => $v !== $id));
        } else {
            $this->selectedPersonIds[] = $id;
        }
    }
};
?>


<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Folders</h1>
    <x-scanline-rule class="w-24 mb-8" />

    <div class="relative mb-6">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-text-faint" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input
            type="text"
            wire:model.live.debounce.200ms="search"
            class="w-full bg-bg border border-border rounded pl-10 pr-4 py-2.5 font-mono text-sm text-text-pri placeholder-text-faint focus:outline-none focus:border-border-light"
            placeholder="Filter people..."
        />
    </div>

    @if ($this->people->isNotEmpty())
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach ($this->people as $person)
                <x-person-chip
                    :person="$person"
                    :selected="in_array($person->id, $selectedPersonIds)"
                />
            @endforeach
        </div>
    @endif

    @if (empty($selectedPersonIds))
        <x-empty-state
            title="Select people above"
            description="Choose one or more people to find photos they appear in together."
        />
    @else
        @php $results = $this->results(); @endphp

        @if ($results->isNotEmpty())
            <p class="font-mono text-xs text-text-muted mb-4">{{ $results->total() }} {{ $results->total() === 1 ? 'photo' : 'photos' }} found</p>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($results as $photo)
                    <div class="bg-surface rounded-lg overflow-hidden border border-border">
                        <div class="aspect-[4/3] bg-surface-alt flex items-center justify-center">
                            <img src="{{ Storage::disk('shared')->url($photo->path) }}" alt="" class="w-full h-full object-cover" loading="lazy" />
                        </div>
                        <div class="p-2">
                            <div class="flex flex-wrap gap-1">
                                @foreach ($photo->faces as $face)
                                    @if ($face->person)
                                        <span class="font-mono text-[10px] text-text-muted">{{ $face->person->name }}</span>
                                        @if (! $loop->last) <span class="text-[10px] text-text-faint">,</span> @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{ $results->links('components.pagination') }}
        @else
            <x-empty-state title="No results" description="No photos found containing all selected people." />
        @endif
    @endif
</div>
