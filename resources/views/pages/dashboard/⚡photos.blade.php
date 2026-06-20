<?php

use App\Models\Photo;
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

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function photos()
    {
        return $this->project->photos()->withCount('faces')->latest()->paginate(12);
    }

    public function deletePhoto(string $id): void
    {
        $photo = Photo::find($id);

        if ($photo) {
            $photo->delete();
        }

        $this->resetPage();

        $this->dispatch('toast', message: 'Photo deleted.', type: 'success');
    }
};
?>


<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Photos</h1>
    <x-scanline-rule class="w-24 mb-8" />

    @php $photos = $this->photos(); @endphp

    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($photos as $photo)
                <x-photo-card :photo="$photo" />
            @endforeach
        </div>

        {{ $photos->links('components.pagination') }}
    @else
        <x-empty-state title="No photos yet" description="Upload some photos to get started." />
    @endif
</div>
