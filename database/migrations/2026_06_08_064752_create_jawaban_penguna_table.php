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
        Schema::create('jawaban_penguna', function (Blueprint $table) {
            $table->id('id_jawaban');
            $table->unsignedBigInteger('riwayat_kuis_id');
            $table->unsignedBigInteger('soal_id');
            $table->char('jawaban_dipilih', 1)->nullable(); // A, B, C, atau D
            $table->string('status'); // correct, incorrect, unanswered
            $table->foreign('riwayat_kuis_id')->references('id')->on('riwayat_kuis')->onDelete('cascade');
            $table->foreign('soal_id')->references('id')->on('soal')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_penguna');
    }
};
