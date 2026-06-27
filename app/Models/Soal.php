<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['kuis_id', 'soal_soal', 'gambar_soal', 'tipe_soal', 'poin', 'urutan', 'jawaban_a', 'jawaban_b', 'jawaban_c', 'jawaban_d', 'jawaban_benar', 'gambar_jawaban_a', 'gambar_jawaban_b', 'gambar_jawaban_c', 'gambar_jawaban_d'])]
class Soal extends Model
{
    protected $table = 'soal';

    /**
     * Kolom gambar yang akan otomatis dikonversi ke URL lengkap di response.
     * Raw path tetap tersimpan di DB, accessor yang mengubahnya ke URL.
     */
    protected $appends = [
        'gambar_soal_url',
        'gambar_jawaban_a_url',
        'gambar_jawaban_b_url',
        'gambar_jawaban_c_url',
        'gambar_jawaban_d_url',
    ];

    // ── Accessors: konversi path → URL lengkap ────────────────────────────

    public function getGambarSoalUrlAttribute(): ?string
    {
        return $this->gambar_soal
            ? Storage::disk('public')->url($this->gambar_soal)
            : null;
    }

    public function getGambarJawabanAUrlAttribute(): ?string
    {
        return $this->gambar_jawaban_a
            ? Storage::disk('public')->url($this->gambar_jawaban_a)
            : null;
    }

    public function getGambarJawabanBUrlAttribute(): ?string
    {
        return $this->gambar_jawaban_b
            ? Storage::disk('public')->url($this->gambar_jawaban_b)
            : null;
    }

    public function getGambarJawabanCUrlAttribute(): ?string
    {
        return $this->gambar_jawaban_c
            ? Storage::disk('public')->url($this->gambar_jawaban_c)
            : null;
    }

    public function getGambarJawabanDUrlAttribute(): ?string
    {
        return $this->gambar_jawaban_d
            ? Storage::disk('public')->url($this->gambar_jawaban_d)
            : null;
    }

    // ── Relations ─────────────────────────────────────────────────────────

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'kuis_id', 'kuis_id');
    }

    public function jawabanPengguna()
    {
        return $this->hasMany(JawabanPengguna::class, 'soal_id');
    }

    /**
     * Get array of available answers with full image URLs
     */
    public function getAnswersArray()
    {
        return [
            ['id' => 'a', 'text' => $this->jawaban_a, 'image' => $this->gambar_jawaban_a_url],
            ['id' => 'b', 'text' => $this->jawaban_b, 'image' => $this->gambar_jawaban_b_url],
            ['id' => 'c', 'text' => $this->jawaban_c, 'image' => $this->gambar_jawaban_c_url],
            ['id' => 'd', 'text' => $this->jawaban_d, 'image' => $this->gambar_jawaban_d_url],
        ];
    }
}
