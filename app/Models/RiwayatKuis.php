<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['guru_id', 'kuis_id', 'waktu_mulai', 'waktu_selesai', 'total_skor', 'status'])]
class RiwayatKuis extends Model
{
    protected $table = 'riwayat_kuis';

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'kuis_id');
    }

    public function jawabanPengguna()
    {
        return $this->hasMany(JawabanPengguna::class, 'riwayat_kuis_id');
    }
}
