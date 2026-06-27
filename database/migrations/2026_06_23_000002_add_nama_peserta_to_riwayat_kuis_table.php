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
        Schema::table('riwayat_kuis', function (Blueprint $table) {
            // Nama peserta yang diinput saat join kuis (tidak perlu akun)
            $table->string('nama_peserta')->nullable()->after('kuis_id');

            // guru_id dijadikan benar-benar opsional karena yang join adalah peserta tanpa akun
            $table->unsignedBigInteger('guru_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kuis', function (Blueprint $table) {
            $table->dropColumn('nama_peserta');
        });
    }
};
