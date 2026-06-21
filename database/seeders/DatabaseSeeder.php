<?php

namespace Database\Seeders;

use App\Models\Guru;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Guru::factory()->create([
            'nama_lengkap' => 'Test Guru',
            'email' => 'test@example.com',
            'mapel' => 'Matematika',
        ]);
    }
}
