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
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kuis_id');
            $table->text('soal_soal');
            $table->string('fungsi_jawaban')->nullable(); // untuk mengidentifikasi tipe fungsi
            $table->string('tipe_soal');
            $table->integer('poin');
            $table->integer('urutan');
            $table->text('jawaban_a');
            $table->text('jawaban_b');
            $table->text('jawaban_c');
            $table->text('jawaban_d');
            $table->char('jawaban_benar', 1); // A, B, C, atau D
            $table->foreign('kuis_id')->references('kuis_id')->on('kuis')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
