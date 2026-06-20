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
        Schema::table('soal', function (Blueprint $table) {
            // Tambah field untuk image di setiap pilihan jawaban
            $table->string('gambar_soal')->nullable()->after('soal_soal');
            $table->string('gambar_jawaban_a')->nullable()->after('jawaban_a');
            $table->string('gambar_jawaban_b')->nullable()->after('jawaban_b');
            $table->string('gambar_jawaban_c')->nullable()->after('jawaban_c');
            $table->string('gambar_jawaban_d')->nullable()->after('jawaban_d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('soal', function (Blueprint $table) {
            $table->dropColumn(['gambar_soal', 'gambar_jawaban_a', 'gambar_jawaban_b', 'gambar_jawaban_c', 'gambar_jawaban_d']);
        });
    }
};
