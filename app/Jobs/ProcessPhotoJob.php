<?php

namespace App\Jobs;

use App\Events\PhotoProcessingProgress;
use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Services\FacePipelineService;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoJob implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(
        public PhotoBatch $batch,
        public Photo $photo,
    ) {}

    public function handle(FacePipelineService $service): void
    {
        $absolutePath = Storage::disk('shared')->path($this->photo->path);

        $service->processPhoto(
            $this->photo->project_id,
            $this->photo->id,
            $absolutePath,
        );

        $this->photo->update(['status' => 'processed']);

        $this->batch->increment('processed_photos');
        $this->batch->refresh();

        event(new PhotoProcessingProgress(
            batchId: $this->batch->id,
            projectId: $this->photo->project_id,
            photoId: $this->photo->id,
            processedCount: $this->batch->processed_photos,
            totalCount: $this->batch->total_photos,
        ));
    }
}
