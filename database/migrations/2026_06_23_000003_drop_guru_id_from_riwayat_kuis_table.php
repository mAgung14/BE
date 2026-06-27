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
            // Hapus foreign key dulu sebelum drop kolom
            $table->dropForeign(['guru_id']);
            $table->dropColumn('guru_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_kuis', function (Blueprint $table) {
            $table->unsignedBigInteger('guru_id')->nullable()->after('id');
            $table->foreign('guru_id')
                  ->references('id')
                  ->on('guru')
                  ->onDelete('set null');
        });
    }
};
