<?php

use App\Models\Photo;
use App\Models\Project;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    use WithFileUploads;

    public Project $project;

    public $newPhotos = [];

    public function mount(Project $project): void
    {
    }

    public function updatedNewPhotos(): void
    {
        $batch = $this->project->photoBatches()->latest()->first();

        foreach ($this->newPhotos as $file) {
            Photo::create([
                'project_id' => $this->project->id,
                'batch_id' => $batch?->id,
                'path' => $file->store('photos', 'public'),
                'status' => 'pending',
            ]);
        }

        $this->newPhotos = [];
    }
};
?>

@php
    $batch = $project->photoBatches()->latest()->first();
    $photos = $batch?->photos()->latest()->get() ?? collect();
@endphp

<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Upload Photos</h1>
    <x-scanline-rule class="w-24 mb-8" />

    <x-dropzone accept="image/*" />

    @if ($photos->isNotEmpty())
        <h2 class="font-mono text-sm font-medium text-text-pri mt-10 mb-4">Queued</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($photos as $photo)
                <div class="relative bg-surface rounded-lg overflow-hidden border border-border">
                    <div class="aspect-[4/3] bg-surface-alt flex items-center justify-center">
                        <img src="{{ asset($photo->path) }}" alt="" class="w-full h-full object-cover" loading="lazy" />
                    </div>
                    <span class="absolute top-2 left-2 px-2 py-0.5 bg-bg/80 backdrop-blur-sm rounded text-[10px] font-mono uppercase tracking-wider text-text-faint">
                        Queued
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
