<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['kuis_id', 'nama_peserta', 'waktu_mulai', 'waktu_selesai', 'total_skor', 'status'])]
class RiwayatKuis extends Model
{
    protected $table = 'riwayat_kuis';

    protected $appends = ['durasi', 'jumlah_benar'];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'kuis_id');
    }

    public function jawabanPengguna()
    {
        return $this->hasMany(JawabanPengguna::class, 'riwayat_kuis_id');
    }

    /**
     * Hitung durasi pengerjaan (waktu_selesai - waktu_mulai) dalam format MM:SS
     */
    public function getDurasiAttribute(): ?string
    {
        if (!$this->waktu_mulai || !$this->waktu_selesai) {
            return null;
        }

        $mulai   = Carbon::parse($this->waktu_mulai);
        $selesai = Carbon::parse($this->waktu_selesai);
        $detik   = max(0, $selesai->diffInSeconds($mulai));

        $menit  = intdiv($detik, 60);
        $sisa   = $detik % 60;

        return sprintf('%02d:%02d', $menit, $sisa);
    }

    /**
     * Hitung total jawaban benar dari tabel jawaban_penguna
     */
    public function getJumlahBenarAttribute(): int
    {
        return $this->jawabanPengguna()->where('status', 'correct')->count();
    }
}
