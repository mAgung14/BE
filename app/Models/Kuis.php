<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['guru_id', 'judul', 'kategori', 'soal_waktu', 'perm_istirahat', 'tgl_dibuat'])]
class Kuis extends Model
{
    protected $table = 'kuis';
    protected $primaryKey = 'kuis_id';
    public $incrementing = true;
    protected $keyType = 'int';

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }
}
