<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhotoProcessingProgress implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $batchId,
        public string $projectId,
        public string $photoId,
        public int $processedCount,
        public int $totalCount,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("project.{$this->projectId}");
    }

    public function broadcastAs(): string
    {
        return 'photo.processed';
    }
}
