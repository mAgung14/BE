<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesertaBergabung implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $kuisId,
        public readonly array $pesertaList
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("kuis.{$this->kuisId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'peserta.bergabung';
    }

    public function broadcastWith(): array
    {
        return [
            'kuis_id' => $this->kuisId,
            'peserta' => $this->pesertaList,
            'message' => 'Daftar peserta diperbarui.',
        ];
    }
}
