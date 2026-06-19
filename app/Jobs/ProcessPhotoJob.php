<?php

namespace App\Jobs;

use App\Events\PhotoProcessingProgress;
use App\Models\Face;
use App\Models\Photo;
use App\Models\PhotoBatch;
use App\Services\FacePipelineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class ProcessPhotoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PhotoBatch $batch,
        public Photo $photo,
    ) {}

    public function handle(FacePipelineService $service): void
    {
        $absolutePath = Storage::disk('shared')->path($this->photo->path);

        $result = $service->processPhoto(
            $this->photo->project_id,
            $this->photo->id,
            $absolutePath,
        );

        foreach ($result['faces'] as $faceData) {
            Face::create([
                'photo_id' => $this->photo->id,
                'person_id' => $faceData['person_id'],
                'cluster_id' => null,
                'bbox' => $faceData['bbox'],
                'crop_path' => $faceData['crop_path'],
                'det_score' => $faceData['det_score'],
            ]);
        }

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
