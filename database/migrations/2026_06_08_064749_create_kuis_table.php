<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id('kuis_id');
            $table->string('judul');
            $table->string('kategori');
            $table->integer('soal_waktu'); // dalam menit
            $table->integer('perm_istirahat')->default(0); // dalam detik
            $table->timestamp('tgl_dibuat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuis');
    }
};
