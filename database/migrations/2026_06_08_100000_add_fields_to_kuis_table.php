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
        Schema::table('kuis', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('judul');
            $table->enum('akses', ['publik', 'private'])->default('private')->after('is_published');
            $table->string('kode_kuis', 10)->unique()->nullable()->after('akses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuis', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'akses', 'kode_kuis']);
        });
    }
};
