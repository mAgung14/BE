<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KuisDimulai implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $kuisId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("kuis.{$this->kuisId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kuis.dimulai';
    }

    public function broadcastWith(): array
    {
        return [
            'kuis_id' => $this->kuisId,
            'message' => 'Kuis telah dimulai oleh guru.',
        ];
    }
}
