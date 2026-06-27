<?php

namespace App\Http\Controllers\Api;

use App\Exports\KuisTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportKuisRequest;
use App\Http\Requests\StoreKuisRequest;
use App\Http\Requests\UpdateKuisRequest;
use App\Imports\KuisImport;
use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KuisController extends Controller
{
    /**
     * Display a listing of quizzes with filters and search
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $status = $request->query('status', 'semua');
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        if ($user) {
            $query = Kuis::byGuru($user->id)
                ->byStatus($status)
                ->search($search)
                ->orderByDesc('created_at');
        } else {
            $query = Kuis::where('akses', 'publik')
                ->where('is_published', true)
                ->search($search)
                ->orderByDesc('created_at');
        }

        $kuis = $query->paginate($perPage);

        // Tambah info tambahan untuk setiap kuis
        $kuis->getCollection()->transform(function ($item) {
            return [
                'kuis_id' => $item->kuis_id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'kategori' => $item->kategori,
                'status' => $item->status,
                'akses' => $item->akses,
                'kode_kuis' => $item->kode_kuis,
                'soal_waktu' => $item->soal_waktu,
                'jumlah_soal' => $item->countSoal(),
                'total_poin' => $item->getTotalPoin(),
                'tgl_dibuat' => $item->tgl_dibuat,
                'is_published' => $item->is_published,
            ];
        });

        return response()->json([
            'message' => 'Data kuis berhasil diambil.',
            'data' => $kuis->items(),
            'pagination' => [
                'total' => $kuis->total(),
                'per_page' => $kuis->perPage(),
                'current_page' => $kuis->currentPage(),
                'last_page' => $kuis->lastPage(),
            ],
        ]);
    }

    /**
     * Display a listing of public published quizzes
     */
    public function publicList(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        $query = Kuis::where('akses', 'publik')
            ->where('is_published', true)
            ->search($search)
            ->orderByDesc('created_at');

        $kuis = $query->paginate($perPage);

        // Tambah info tambahan untuk setiap kuis
        $kuis->getCollection()->transform(function ($item) {
            return [
                'kuis_id' => $item->kuis_id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'kategori' => $item->kategori,
                'status' => $item->status,
                'akses' => $item->akses,
                'kode_kuis' => $item->kode_kuis,
                'soal_waktu' => $item->soal_waktu,
                'jumlah_soal' => $item->countSoal(),
                'total_poin' => $item->getTotalPoin(),
                'tgl_dibuat' => $item->tgl_dibuat,
                'is_published' => $item->is_published,
            ];
        });

        return response()->json([
            'message' => 'Data kuis publik berhasil diambil.',
            'data' => $kuis->items(),
            'pagination' => [
                'total' => $kuis->total(),
                'per_page' => $kuis->perPage(),
                'current_page' => $kuis->currentPage(),
                'last_page' => $kuis->lastPage(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKuisRequest $request): JsonResponse
    {
        $validated = $request->validated();
        
        $akses = $validated['akses'] ?? 'private';
        $kodeKuis = ($akses === 'publik') ? null : Kuis::generateKodeKuis();

        $kuis = Kuis::create([
            'guru_id' => $request->user()->id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'] ?? '',
            'kategori' => $validated['kategori'],
            'soal_waktu' => $validated['soal_waktu'],
            'perm_istirahat' => $validated['perm_istirahat'] ?? 0,
            'tgl_dibuat' => $validated['tgl_dibuat'] ?? now(),
            'akses' => $akses,
            'kode_kuis' => $kodeKuis,
            'status' => 'draft',
            'is_published' => false,
        ]);

        return response()->json([
            'message' => 'Kuis berhasil dibuat.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'judul' => $kuis->judul,
                'kode_kuis' => $kuis->kode_kuis,
                'akses' => $kuis->akses,
                'status' => $kuis->status,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $kuis = Kuis::with(['soal', 'guru'])->findOrFail($id);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        return response()->json([
            'message' => 'Detail kuis berhasil diambil.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'judul' => $kuis->judul,
                'deskripsi' => $kuis->deskripsi,
                'kategori' => $kuis->kategori,
                'status' => $kuis->status,
                'akses' => $kuis->akses,
                'kode_kuis' => $kuis->kode_kuis,
                'soal_waktu' => $kuis->soal_waktu,
                'perm_istirahat' => $kuis->perm_istirahat,
                'is_published' => $kuis->is_published,
                'jumlah_soal' => $kuis->countSoal(),
                'total_poin' => $kuis->getTotalPoin(),
                'guru' => [
                    'id' => $kuis->guru->id,
                    'name' => $kuis->guru->name,
                    'email' => $kuis->guru->email,
                ],
                'soal' => $kuis->soal->map(function ($soal) {
                    return [
                        'id' => $soal->id,
                        'urutan' => $soal->urutan,
                        'soal_soal' => $soal->soal_soal,
                        'gambar_soal' => $soal->gambar_soal,
                        'tipe_soal' => $soal->tipe_soal,
                        'poin' => $soal->poin,
                        'jawaban_a' => $soal->jawaban_a,
                        'jawaban_b' => $soal->jawaban_b,
                        'jawaban_c' => $soal->jawaban_c,
                        'jawaban_d' => $soal->jawaban_d,
                        'gambar_jawaban_a' => $soal->gambar_jawaban_a,
                        'gambar_jawaban_b' => $soal->gambar_jawaban_b,
                        'gambar_jawaban_c' => $soal->gambar_jawaban_c,
                        'gambar_jawaban_d' => $soal->gambar_jawaban_d,
                        'jawaban_benar' => $soal->jawaban_benar,
                    ];
                }),
                'tgl_dibuat' => $kuis->tgl_dibuat,
                'created_at' => $kuis->created_at,
                'updated_at' => $kuis->updated_at,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateKuisRequest $request, string $id): JsonResponse
    {
        $kuis = Kuis::findOrFail($id);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        $validated = $request->validated();

        $akses = $validated['akses'] ?? $kuis->akses;
        $kodeKuis = $kuis->kode_kuis;
        if (isset($validated['akses'])) {
            if ($akses === 'publik') {
                $kodeKuis = null;
            } elseif ($akses === 'private' && is_null($kodeKuis)) {
                $kodeKuis = Kuis::generateKodeKuis();
            }
        }

        $kuis->update([
            'judul' => $validated['judul'] ?? $kuis->judul,
            'deskripsi' => $validated['deskripsi'] ?? $kuis->deskripsi,
            'kategori' => $validated['kategori'] ?? $kuis->kategori,
            'soal_waktu' => $validated['soal_waktu'] ?? $kuis->soal_waktu,
            'perm_istirahat' => $validated['perm_istirahat'] ?? $kuis->perm_istirahat,
            'akses' => $akses,
            'kode_kuis' => $kodeKuis,
            'status' => $validated['status'] ?? $kuis->status,
            'is_published' => $validated['is_published'] ?? $kuis->is_published,
        ]);

        return response()->json([
            'message' => 'Kuis berhasil diperbarui.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'judul' => $kuis->judul,
                'status' => $kuis->status,
                'akses' => $kuis->akses,
                'kode_kuis' => $kuis->kode_kuis,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $kuis = Kuis::findOrFail($id);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        $judul = $kuis->judul;
        $kuis->delete();

        return response()->json([
            'message' => "Kuis '{$judul}' berhasil dihapus.",
        ]);
    }

    /**
     * Summary dashboard for authenticated guru.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $total = Kuis::where('guru_id', $user->id)->count();
        $active = Kuis::where('guru_id', $user->id)->where('status', 'aktif')->count();
        $finished = Kuis::where('guru_id', $user->id)->where('status', 'selesai')->count();

        $latest = Kuis::where('guru_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['kuis_id', 'judul', 'kategori', 'status', 'tgl_dibuat', 'created_at']);

        return response()->json([
            'data' => [
                'total_kuis' => $total,
                'kuis_aktif' => $active,
                'kuis_selesai' => $finished,
                'latest' => $latest,
            ],
        ]);
    }

    /**
     * Publish/unpublish quiz
     */
    public function publish(string $id): JsonResponse
    {
        $kuis = Kuis::findOrFail($id);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        // Check if quiz has questions
        if ($kuis->countSoal() === 0) {
            return response()->json([
                'message' => 'Kuis harus memiliki minimal 1 soal untuk dipublikasikan.',
            ], 422);
        }

        $kuis->update([
            'is_published' => true,
            'status' => 'aktif',
        ]);

        return response()->json([
            'message' => 'Kuis berhasil dipublikasikan.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'is_published' => $kuis->is_published,
                'status' => $kuis->status,
            ],
        ]);
    }

    /**
     * Import quiz from Excel
     *
     * Struktur file Excel:
     *  Baris 1 : Header kuis  [Judul, Deskripsi, Kategori, Waktu(menit), Istirahat(detik), Akses]
     *  Baris 2+: Soal-soal   [Pertanyaan, Poin, JawA, JawB, JawC, JawD, JawabanBenar(a/b/c/d)]
     */
    public function importExcel(ImportKuisRequest $request): JsonResponse
    {
        try {
            $import = new KuisImport(auth()->id());
            Excel::import($import, $request->file('file'));

            return response()->json([
                'message' => 'Kuis berhasil diimpor dari Excel.',
                'data' => [
                    'kuis_id'     => $import->kuis->kuis_id,
                    'judul'       => $import->kuis->judul,
                    'jumlah_soal' => $import->jumlahSoalDiimpor,
                    'kode_kuis'   => $import->kuis->kode_kuis,
                    'akses'       => $import->kuis->akses,
                    'status'      => $import->kuis->status,
                ],
            ], 201);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi file Excel gagal.',
                'errors'  => $e->failures(),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => 'Format file Excel tidak valid: ' . $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimpor file Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download template file Excel untuk import kuis.
     *
     * Melayani file pre-generated dari storage untuk performa optimal.
     * File di-regenerate otomatis jika belum ada.
     */
    public function downloadTemplate()
    {
        $path     = 'templates/template-import-kuis.xlsx';
        $filename = 'template-import-kuis.xlsx';

        // Jika file sudah ada, sajikan langsung (lebih cepat)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return \Illuminate\Support\Facades\Storage::disk('public')->download($path, $filename);
        }

        // Generate on-the-fly jika file belum ada
        return Excel::download(new KuisTemplateExport(), $filename);
    }

    /**
     * Get detail and questions of a published public quiz by ID.
     */
    public function publicShow(string $id): JsonResponse
    {
        $kuis = Kuis::with(['soal', 'guru'])->findOrFail($id);

        if ($kuis->akses !== 'publik' || !$kuis->is_published) {
            return response()->json([
                'message' => 'Kuis tidak ditemukan atau belum dipublikasikan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail kuis publik berhasil diambil.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'judul' => $kuis->judul,
                'deskripsi' => $kuis->deskripsi,
                'kategori' => $kuis->kategori,
                'status' => $kuis->status,
                'akses' => $kuis->akses,
                'soal_waktu' => $kuis->soal_waktu,
                'perm_istirahat' => $kuis->perm_istirahat,
                'jumlah_soal' => $kuis->countSoal(),
                'total_poin' => $kuis->getTotalPoin(),
                'guru' => [
                    'name' => $kuis->guru->nama_lengkap ?? '',
                ],
                'soal' => $kuis->soal->map(function ($soal) {
                    return [
                        'id' => $soal->id,
                        'urutan' => $soal->urutan,
                        'soal_soal' => $soal->soal_soal,
                        'gambar_soal' => $soal->gambar_soal,
                        'tipe_soal' => $soal->tipe_soal,
                        'poin' => $soal->poin,
                        'jawaban_a' => $soal->jawaban_a,
                        'jawaban_b' => $soal->jawaban_b,
                        'jawaban_c' => $soal->jawaban_c,
                        'jawaban_d' => $soal->jawaban_d,
                        'gambar_jawaban_a' => $soal->gambar_jawaban_a,
                        'gambar_jawaban_b' => $soal->gambar_jawaban_b,
                        'gambar_jawaban_c' => $soal->gambar_jawaban_c,
                        'gambar_jawaban_d' => $soal->gambar_jawaban_d,
                        'jawaban_benar' => $soal->jawaban_benar,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Join a private/public quiz by its code.
     */
    public function joinByCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_kuis' => 'required|string',
        ]);

        $kuis = Kuis::with(['soal', 'guru'])
            ->where('kode_kuis', strtoupper($validated['kode_kuis']))
            ->first();

        if (!$kuis) {
            return response()->json([
                'message' => 'Kode kuis tidak valid atau kuis tidak ditemukan.',
            ], 404);
        }

        if (!$kuis->is_published) {
            return response()->json([
                'message' => 'Kuis belum dipublikasikan oleh guru.',
            ], 403);
        }

        return response()->json([
            'message' => 'Berhasil bergabung dengan kuis.',
            'data' => [
                'kuis_id' => $kuis->kuis_id,
                'judul' => $kuis->judul,
                'deskripsi' => $kuis->deskripsi,
                'kategori' => $kuis->kategori,
                'status' => $kuis->status,
                'akses' => $kuis->akses,
                'soal_waktu' => $kuis->soal_waktu,
                'perm_istirahat' => $kuis->perm_istirahat,
                'jumlah_soal' => $kuis->countSoal(),
                'total_poin' => $kuis->getTotalPoin(),
                'guru' => [
                    'name' => $kuis->guru->nama_lengkap ?? '',
                ],
                'soal' => $kuis->soal->map(function ($soal) {
                    return [
                        'id' => $soal->id,
                        'urutan' => $soal->urutan,
                        'soal_soal' => $soal->soal_soal,
                        'gambar_soal' => $soal->gambar_soal,
                        'tipe_soal' => $soal->tipe_soal,
                        'poin' => $soal->poin,
                        'jawaban_a' => $soal->jawaban_a,
                        'jawaban_b' => $soal->jawaban_b,
                        'jawaban_c' => $soal->jawaban_c,
                        'jawaban_d' => $soal->jawaban_d,
                        'gambar_jawaban_a' => $soal->gambar_jawaban_a,
                        'gambar_jawaban_b' => $soal->gambar_jawaban_b,
                        'gambar_jawaban_c' => $soal->gambar_jawaban_c,
                        'gambar_jawaban_d' => $soal->gambar_jawaban_d,
                        'jawaban_benar' => $soal->jawaban_benar,
                    ];
                }),
            ],
        ]);
    }
}
