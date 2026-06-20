<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['riwayat_kuis_id', 'soal_id', 'jawaban_dipilih', 'status'])]
class JawabanPengguna extends Model
{
    protected $table = 'jawaban_penguna';
    protected $primaryKey = 'id_jawaban';

    public function riwayatKuis()
    {
        return $this->belongsTo(RiwayatKuis::class, 'riwayat_kuis_id');
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }

    /**
     * Check if answer is correct
     */
    public function isCorrect()
    {
        return $this->jawaban_dipilih === $this->soal->jawaban_benar;
    }
}
