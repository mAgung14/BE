<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PesertaSubmitKuis implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int    $guruId,
        public readonly int    $kuisId,
        public readonly string $judulKuis,
        public readonly string $namaPeserta,
        public readonly int    $totalSkor,
        public readonly string $durasi,
        public readonly string $waktuSelesai,
    ) {}

    /**
     * Broadcast ke private channel milik guru.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("guru.{$this->guruId}"),
        ];
    }

    /**
     * Nama event yang diterima di frontend.
     */
    public function broadcastAs(): string
    {
        return 'peserta.submit';
    }

    /**
     * Data yang dikirim ke frontend.
     */
    public function broadcastWith(): array
    {
        return [
            'kuis_id'      => $this->kuisId,
            'judul_kuis'   => $this->judulKuis,
            'nama_peserta' => $this->namaPeserta,
            'total_skor'   => $this->totalSkor,
            'durasi'       => $this->durasi,
            'waktu_selesai'=> $this->waktuSelesai,
        ];
    }
}