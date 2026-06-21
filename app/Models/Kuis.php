<?php

namespace App\Models;

use App\Models\Guru;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['guru_id', 'judul', 'deskripsi', 'kategori', 'soal_waktu', 'perm_istirahat', 'tgl_dibuat', 'status', 'is_published', 'akses', 'kode_kuis'])]
class Kuis extends Model
{
    protected $table = 'kuis';
    protected $primaryKey = 'kuis_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function soal()
    {
        return $this->hasMany(Soal::class, 'kuis_id', 'kuis_id');
    }

    public function riwayatKuis()
    {
        return $this->hasMany(RiwayatKuis::class, 'kuis_id', 'kuis_id');
    }

    /**
     * Generate unique quiz code
     */
    public static function generateKodeKuis()
    {
        $kode = strtoupper(substr(uniqid(), -6));
        while (self::where('kode_kuis', $kode)->exists()) {
            $kode = strtoupper(substr(uniqid(), -6));
        }
        return $kode;
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'semua') {
            return $query->where('status', $status);
        }
        return $query;
    }

    /**
     * Scope: Filter by guru
     */
    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Scope: Search by title
     */
    public function scopeSearch($query, $keyword)
    {
        if ($keyword) {
            return $query->where('judul', 'like', "%{$keyword}%")
                        ->orWhere('deskripsi', 'like', "%{$keyword}%");
        }
        return $query;
    }

    /**
     * Get count of questions
     */
    public function countSoal()
    {
        return $this->soal()->count();
    }

    /**
     * Get total points
     */
    public function getTotalPoin()
    {
        return $this->soal()->sum('poin');
    }
}
