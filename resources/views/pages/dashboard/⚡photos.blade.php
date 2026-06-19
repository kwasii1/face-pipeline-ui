<?php

use App\Models\Photo;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
    }

    public function deletePhoto(string $id): void
    {
        Photo::find($id)?->delete();
    }
};
?>

@php
    $photos = $project->photos()->withCount('faces')->latest()->get();
@endphp

<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Photos</h1>
    <x-scanline-rule class="w-24 mb-8" />

    @if ($photos->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($photos as $photo)
                <x-photo-card :photo="$photo" />
            @endforeach
        </div>
    @else
        <x-empty-state title="No photos yet" description="Upload some photos to get started." />
    @endif
</div>
