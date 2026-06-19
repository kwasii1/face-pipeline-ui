<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacePipelineService
{
    /**
     * @return array{photo_id: string, faces_detected: int, faces_filtered_out: int, faces: array}
     */
    public function processPhoto(string $projectId, string $photoId, string $photoPath): array
    {
        return Http::timeout(120)
            ->post(config('services.fastapi.base_url').'/process-photo', [
                'project_id' => $projectId,
                'photo_id' => $photoId,
                'photo_path' => $photoPath,
            ])
            ->throw()
            ->json();
    }

    /**
     * @return array{faces_considered: int, clusters_found: int, noise_count: int, cluster_ids: string[]}
     */
    public function clusterUnassigned(string $projectId): array
    {
        return Http::timeout(300)
            ->post(config('services.fastapi.base_url').'/cluster-unassigned', [
                'project_id' => $projectId,
            ])
            ->throw()
            ->json();
    }
}
