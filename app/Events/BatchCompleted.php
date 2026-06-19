<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BatchCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $batchId,
        public string $projectId,
        public int $totalPhotos,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel("project.{$this->projectId}");
    }

    public function broadcastAs(): string
    {
        return 'batch.completed';
    }
}
