<?php

namespace App\Jobs;

use App\Events\BatchCompleted;
use App\Models\Face;
use App\Models\Person;
use App\Models\PersonCentroid;
use App\Models\Photo;
use App\Models\PhotoBatch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;

class ReprocessProjectJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $projectId,
    ) {}

    public function handle(): void
    {
        $faces = Face::whereHas('photo', fn ($q) => $q->where('project_id', $this->projectId))->get();

        foreach ($faces as $face) {
            $face->delete();
        }

        PersonCentroid::whereHas('person', fn ($q) => $q->where('project_id', $this->projectId))->delete();

        Person::where('project_id', $this->projectId)->delete();

        Photo::where('project_id', $this->projectId)->update(['status' => 'pending']);

        $photos = Photo::where('project_id', $this->projectId)->get();

        if ($photos->isEmpty()) {
            return;
        }

        $batch = PhotoBatch::create([
            'project_id' => $this->projectId,
            'total_photos' => $photos->count(),
            'processed_photos' => 0,
            'status' => 'processing',
        ]);

        $jobs = $photos->map(fn (Photo $photo) => new ProcessPhotoJob($batch, $photo))->all();

        Bus::batch($jobs)
            ->then(function () use ($batch) {
                $batch->update(['status' => 'completed']);

                BatchCompleted::dispatch(
                    batchId: $batch->id,
                    projectId: $this->projectId,
                    totalPhotos: $batch->total_photos,
                );
            })
            ->catch(function () use ($batch) {
                $batch->update(['status' => 'failed']);
            })
            ->finally(function () use ($batch) {
                ClusterUnassignedJob::dispatch($batch);
            })
            ->onQueue('photos')
            ->dispatch();
    }
}
