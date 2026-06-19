<?php

use App\Models\Project;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public Project $project;

    public ?object $batch = null;

    public function mount(Project $project): void
    {
        $this->refreshBatch();
    }

    #[On('echo:project.{project.id},.photo.processed')]
    public function onPhotoProcessed(): void
    {
        $this->refreshBatch();
    }

    #[On('echo:project.{project.id},.batch.completed')]
    public function onBatchCompleted(): void
    {
        $this->refreshBatch();
    }

    private function refreshBatch(): void
    {
        $this->batch = $this->project->photoBatches()->latest()->first();
    }
};
?>

<div>
@if ($batch)
    <x-progress-bar
        :current="$batch->processed_photos"
        :total="$batch->total_photos"
        :status="$batch->status"
        {{ $attributes }}
    />
@endif
</div>
