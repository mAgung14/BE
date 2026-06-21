<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kuis;
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

    /**
     * Test public users can list public quizzes.
     */
    public function test_public_user_can_view_published_public_quizzes(): void
    {
        $guru = Guru::factory()->create();

        // 1. Create a published public quiz
        $publicQuiz = Kuis::create([
            'guru_id' => $guru->id,
            'judul' => 'Kuis Publik Matematika',
            'kategori' => 'Matematika',
            'soal_waktu' => 30,
            'tgl_dibuat' => now(),
            'akses' => 'publik',
            'is_published' => true,
            'status' => 'aktif',
            'kode_kuis' => 'PUB123',
        ]);

        // 2. Create a private quiz
        $privateQuiz = Kuis::create([
            'guru_id' => $guru->id,
            'judul' => 'Kuis Private Fisika',
            'kategori' => 'Fisika',
            'soal_waktu' => 30,
            'tgl_dibuat' => now(),
            'akses' => 'private',
            'is_published' => true,
            'status' => 'aktif',
            'kode_kuis' => 'PRIV12',
        ]);

        // 3. Make guest request to /api/kuis/publik
        $response1 = $this->getJson('/api/kuis/publik');
        $response1->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Kuis Publik Matematika');

        // 4. Make guest request to /api/kuis (which should also fall back to public list)
        $response2 = $this->getJson('/api/kuis');
        $response2->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.judul', 'Kuis Publik Matematika');
    }

    /**
     * Test public quiz does not get code and private gets code.
     */
    public function test_public_quiz_does_not_get_code_and_private_gets_code(): void
    {
        $guru = Guru::factory()->create();
        $token = \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::fromUser($guru);

        // 1. Create public quiz
        $responsePublic = $this->postJson('/api/kuis', [
            'judul' => 'Kuis Publik Fisika',
            'kategori' => 'Fisika',
            'soal_waktu' => 45,
            'akses' => 'publik',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $responsePublic->assertStatus(201)
            ->assertJsonPath('data.kode_kuis', null);

        // 2. Create private quiz
        $responsePrivate = $this->postJson('/api/kuis', [
            'judul' => 'Kuis Private Kimia',
            'kategori' => 'Kimia',
            'soal_waktu' => 45,
            'akses' => 'private',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $responsePrivate->assertStatus(201);
        $this->assertNotNull($responsePrivate->json('data.kode_kuis'));
    }

    /**
     * Test public user can view published public quiz details and questions.
     */
    public function test_public_user_can_view_published_public_quiz_details_and_questions(): void
    {
        $guru = Guru::factory()->create();

        // Create public published quiz
        $publicQuiz = Kuis::create([
            'guru_id' => $guru->id,
            'judul' => 'Kuis Publik Sejarah',
            'kategori' => 'Sejarah',
            'soal_waktu' => 30,
            'tgl_dibuat' => now(),
            'akses' => 'publik',
            'is_published' => true,
            'status' => 'aktif',
            'kode_kuis' => null,
        ]);

        // Add questions to it
        $publicQuiz->soal()->create([
            'soal_soal' => 'Siapa Patih Gajah Mada?',
            'tipe_soal' => 'pilihan_ganda',
            'poin' => 5,
            'urutan' => 1,
            'jawaban_a' => 'A',
            'jawaban_b' => 'B',
            'jawaban_c' => 'C',
            'jawaban_d' => 'D',
            'jawaban_benar' => 'a',
        ]);

        // Make guest request to /api/kuis/publik/{id}
        $response = $this->getJson("/api/kuis/publik/{$publicQuiz->kuis_id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.judul', 'Kuis Publik Sejarah')
            ->assertJsonCount(1, 'data.soal')
            ->assertJsonPath('data.soal.0.soal_soal', 'Siapa Patih Gajah Mada?')
            ->assertJsonPath('data.soal.0.jawaban_benar', 'a');
    }

    /**
     * Test user can join private quiz by code.
     */
    public function test_user_can_join_private_quiz_by_code(): void
    {
        $guru = Guru::factory()->create();

        // Create private published quiz
        $privateQuiz = Kuis::create([
            'guru_id' => $guru->id,
            'judul' => 'Kuis Private Geografi',
            'kategori' => 'Geografi',
            'soal_waktu' => 30,
            'tgl_dibuat' => now(),
            'akses' => 'private',
            'is_published' => true,
            'status' => 'aktif',
            'kode_kuis' => 'GEO789',
        ]);

        // Add questions to it
        $privateQuiz->soal()->create([
            'soal_soal' => 'Gunung tertinggi di dunia?',
            'tipe_soal' => 'pilihan_ganda',
            'poin' => 5,
            'urutan' => 1,
            'jawaban_a' => 'Everest',
            'jawaban_b' => 'K2',
            'jawaban_c' => 'Kilimanjaro',
            'jawaban_d' => 'Fuji',
            'jawaban_benar' => 'a',
        ]);

        // Make guest request to /api/kuis/join
        $response = $this->postJson("/api/kuis/join", [
            'kode_kuis' => 'GEO789',
        ]);
        $response->assertStatus(200)
            ->assertJsonPath('data.judul', 'Kuis Private Geografi')
            ->assertJsonCount(1, 'data.soal')
            ->assertJsonPath('data.soal.0.soal_soal', 'Gunung tertinggi di dunia?')
            ->assertJsonPath('data.soal.0.jawaban_benar', 'a');
    }
}
