<?php

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
};
?>

@php
    $totalPhotos = $project->photos()->count();
    $totalFaces = \App\Models\Face::whereHas('photo', fn ($q) => $q->where('project_id', $project->id))->count();
    $taggedPeople = $project->people()->count();
    $needsReview = \App\Models\Face::whereHas('photo', fn ($q) => $q->where('project_id', $project->id))
        ->whereNull('person_id')
        ->count();

    $untaggedFaces = \App\Models\Face::with('photo')
        ->whereHas('photo', fn ($q) => $q->where('project_id', $project->id))
        ->whereNull('person_id')
        ->latest()
        ->limit(8)
        ->get();
@endphp

<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">{{ $project->name }}</h1>
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
</div>
