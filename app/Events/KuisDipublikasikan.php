<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KuisDipublikasikan implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $guruId,
        public readonly int    $kuisId,
        public readonly string $judulKuis,
        public readonly ?string $kodeKuis,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("guru.{$this->guruId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'kuis.dipublikasikan';
    }

    public function broadcastWith(): array
    {
        return [
            'kuis_id'   => $this->kuisId,
            'judul'     => $this->judulKuis,
            'kode_kuis' => $this->kodeKuis,
        ];
    }
}