<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['kuis_id', 'soal_soal', 'gambar_soal', 'tipe_soal', 'poin', 'urutan', 'jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d', 'jawaban_benar', 'gambar_jawaban_a', 'gambar_jawaban_b', 'gambar_jawaban_c', 'gambar_jawaban_d'])]
class Soal extends Model
{
    protected $table = 'soal';

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'kuis_id');
    }

    public function jawabanPengguna()
    {
        return $this->hasMany(JawabanPengguna::class, 'soal_id');
    }

    /**
     * Get array of available answers with images
     */
    public function getAnswersArray()
    {
        return [
            ['id' => 'a', 'text' => $this->jawaban_a, 'image' => $this->gambar_jawaban_a],
            ['id' => 'b', 'text' => $this->jawaban_b, 'image' => $this->gambar_jawaban_b],
            ['id' => 'c', 'text' => $this->jawaban_c, 'image' => $this->gambar_jawaban_c],
            ['id' => 'd', 'text' => $this->jawaban_d, 'image' => $this->gambar_jawaban_d],
        ];
    }
}
