<?php

use App\Jobs\ClusterUnassignedJob;
use App\Jobs\ProcessPhotoJob;
use App\Jobs\ReprocessProjectJob;
use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Bus;

new
#[Layout('layouts::dashboard-layout')]
class extends Component
{
    use WithFileUploads, WithPagination;

    public Project $project;

    public $newPhotos = [];

    public function mount(Project $project): void
    {
        $this->project = $project;
    }

    public function photos()
    {
        $batch = $this->project->photoBatches()->latest()->first();

        return $batch?->photos()->latest()->paginate(12) ?? collect();
    }

    public function updatedNewPhotos(): void
    {
        $batch = $this->project->photoBatches()
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (! $batch) {
            $batch = PhotoBatch::create([
                'project_id' => $this->project->id,
                'total_photos' => 0,
                'processed_photos' => 0,
                'status' => 'pending',
            ]);
        }

        foreach ($this->newPhotos as $file) {
            $path = $file->store('photos', 'shared');

            Photo::create([
                'project_id' => $this->project->id,
                'batch_id' => $batch->id,
                'path' => $path,
                'status' => 'pending',
            ]);
        }

        $photoIds = Photo::where('batch_id', $batch->id)
            ->where('status', 'pending')
            ->pluck('id');

        $photoCount = $photoIds->count();
        $batch->update([
            'total_photos' => $photoCount,
            'status' => 'processing',
        ]);

        $photoChunks = Photo::whereIn('id', $photoIds)->get();
        $jobs = $photoChunks->map(fn (Photo $photo) => new ProcessPhotoJob($batch, $photo))->all();

        Bus::batch($jobs)
            ->then(function () use ($batch) {
                $batch->update(['status' => 'completed']);
            })
            ->catch(function () use ($batch) {
                $batch->update(['status' => 'failed']);
            })
            ->finally(function () use ($batch) {
                ClusterUnassignedJob::dispatch($batch);
            })
            ->onQueue('photos')
            ->dispatch();

        $this->resetPage();
        $this->newPhotos = [];
    }

    public function retryAll(): void
    {
        ReprocessProjectJob::dispatch($this->project->id);

        $this->dispatch('toast', message: 'Reprocessing all photos. This may take a while.', type: 'success');
    }
};
?>


<div class="p-6 max-w-4xl mx-auto">
    <h1 class="font-mono text-xl font-bold text-text-pri mb-1">Upload Photos</h1>
    <x-scanline-rule class="w-24 mb-8" />

    <x-dropzone accept="image/*" />

    @php $photos = $this->photos(); @endphp

    @if ($photos->isNotEmpty())
        <div class="flex items-center justify-between mt-10 mb-4">
            <h2 class="font-mono text-sm font-medium text-text-pri">Queued</h2>
            <button
                wire:click="retryAll"
                wire:confirm="Reprocess all photos from scratch? This will delete all faces, crops, and person assignments."
                class="px-3 py-1.5 bg-accent text-bg font-mono text-xs font-medium tracking-wider uppercase hover:opacity-90 transition-opacity rounded"
            >
                Retry All
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($photos as $photo)
                <div class="relative bg-surface rounded-lg overflow-hidden border border-border">
                    <div class="aspect-[4/3] bg-surface-alt flex items-center justify-center">
                        <img src="{{ Storage::disk('shared')->url($photo->path) }}" alt="" class="w-full h-full object-cover" loading="lazy" />
                    </div>
                    <span class="absolute top-2 left-2 px-2 py-0.5 bg-bg/80 backdrop-blur-sm rounded text-[10px] font-mono uppercase tracking-wider text-text-faint">
                        {{ $photo->status }}
                    </span>
                </div>
            @endforeach
        </div>

        {{ $photos->links('components.pagination') }}
    @endif
</div>
