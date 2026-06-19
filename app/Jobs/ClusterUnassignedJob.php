<?php

namespace App\Jobs;

use App\Models\PhotoBatch;
use App\Services\FacePipelineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ClusterUnassignedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PhotoBatch $batch,
    ) {}

    public function handle(FacePipelineService $service): void
    {
        $service->clusterUnassigned($this->batch->project_id);

        $this->batch->update(['status' => 'clustered']);
    }
}
