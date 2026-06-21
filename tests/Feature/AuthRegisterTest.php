<?php

namespace Tests\Feature;

use App\Models\Guru;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test registration without specifying mapel.
     */
    public function test_user_can_register_and_automatically_becomes_guru_without_mapel(): void
    {
        $payload = [
            'name' => 'Pak Budi',
            'email' => 'budi@guru.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'mapel'],
                    'token',
                    'token_type',
                    'expires_in',
                ],
            ])
            ->assertJsonPath('data.user.role', 'guru')
            ->assertJsonPath('data.user.name', 'Pak Budi')
            ->assertJsonPath('data.user.mapel', null);

        // Assert guru exists in guru table
        $this->assertDatabaseHas('guru', [
            'email' => 'budi@guru.com',
            'nama_lengkap' => 'Pak Budi',
            'mapel' => null,
            'role' => 'guru',
        ]);
    }

    /**
     * Test registration specifying mapel.
     */
    public function test_user_can_register_and_automatically_becomes_guru_with_mapel(): void
    {
        $payload = [
            'name' => 'Ibu Siti',
            'email' => 'siti@guru.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'mapel' => 'Matematika',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.user.role', 'guru')
            ->assertJsonPath('data.user.name', 'Ibu Siti')
            ->assertJsonPath('data.user.mapel', 'Matematika');

        // Assert guru exists in guru table
        $this->assertDatabaseHas('guru', [
            'email' => 'siti@guru.com',
            'nama_lengkap' => 'Ibu Siti',
            'mapel' => 'Matematika',
            'role' => 'guru',
        ]);
    }

    /**
     * Test login.
     */
    public function test_user_can_login(): void
    {
        $guru = Guru::factory()->create([
            'email' => 'login@test.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'mapel'],
                    'token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    /**
     * Test creating a quiz and adding questions.
     */
    public function test_guru_can_create_quiz_and_add_questions(): void
    {
        $guru = Guru::factory()->create([
            'email' => 'author@test.com',
            'password' => 'password123',
        ]);

        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($guru);

        // 1. Create quiz
        $quizResponse = $this->postJson('/api/kuis', [
            'judul' => 'Kuis Sejarah',
            'deskripsi' => 'Sejarah Indonesia',
            'kategori' => 'Sejarah',
            'soal_waktu' => 60,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $quizResponse->assertStatus(201);
        $kuisId = $quizResponse->json('data.kuis_id');

        // 2. Add question to the quiz
        $questionResponse = $this->postJson('/api/soal', [
            'kuis_id' => $kuisId,
            'soal_soal' => 'Siapa presiden pertama Indonesia?',
            'tipe_soal' => 'pilihan_ganda',
            'poin' => 10,
            'jawaban_a' => 'Soekarno',
            'jawaban_b' => 'Soeharto',
            'jawaban_c' => 'B.J. Habibie',
            'jawaban_d' => 'Gus Dur',
            'jawaban_benar' => 'a',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $questionResponse->assertStatus(201)
            ->assertJsonPath('data.soal_soal', 'Siapa presiden pertama Indonesia?');
    }
}
