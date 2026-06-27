<?php

namespace App\Http\Controllers\Api;

use App\Events\PesertaSubmitKuis;
use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kuis;
use App\Models\RiwayatKuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HasilKuisController extends Controller
{
    /**
     * Ambil semua hasil peserta untuk satu kuis.
     * Hanya bisa diakses oleh guru pemilik kuis.
     *
     * GET /kuis/{kuisId}/hasil
     * Query params: search (nama peserta), per_page
     */
    public function index(Request $request, string $kuisId): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuisId);

        // Hanya guru pemilik kuis yang boleh melihat hasil
        if ($kuis->guru_id !== auth('api')->id()) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke hasil kuis ini.',
            ], 403);
        }

        $search  = $request->query('search', '');
        $perPage = $request->query('per_page', 10);

        // Query semua riwayat kuis yang sudah selesai
        $query = RiwayatKuis::with(['jawabanPengguna'])
            ->where('kuis_id', $kuisId)
            ->where('status', 'completed');

        if ($search) {
            $query->where('nama_peserta', 'like', "%{$search}%");
        }

        $totalSoal   = $kuis->countSoal();
        $allRiwayat  = RiwayatKuis::where('kuis_id', $kuisId)
                            ->where('status', 'completed')
                            ->get();

        // ── Hitung statistik ─────────────────────────────────────────────
        $totalPeserta = $allRiwayat->count();

        // Rata-rata nilai = (rata-rata jumlah_benar / total_soal) * 100
        $rataRataNilai = 0;
        if ($totalPeserta > 0 && $totalSoal > 0) {
            $totalBenarSum = $allRiwayat->sum(function ($r) {
                return $r->jawabanPengguna->where('status', 'correct')->count();
            });
            $rataRataNilai = round(($totalBenarSum / ($totalPeserta * $totalSoal)) * 100, 1);
        }

        // Waktu tercepat (durasi terpendek dari peserta yang sudah selesai)
        $waktuTercepat = null;
        $tercepat = $allRiwayat
            ->filter(fn($r) => $r->waktu_mulai && $r->waktu_selesai)
            ->sortBy('durasi')
            ->first();
        if ($tercepat) {
            $waktuTercepat = $tercepat->durasi;
        }

        // Rata-rata waktu pengerjaan semua peserta (dalam format MM:SS)
        $rataRataWaktu = null;
        $riwayatDenganWaktu = $allRiwayat->filter(
            fn($r) => $r->waktu_mulai && $r->waktu_selesai
        );
        if ($riwayatDenganWaktu->isNotEmpty()) {
            $totalDetik = $riwayatDenganWaktu->sum(function ($r) {
                return max(0, \Carbon\Carbon::parse($r->waktu_selesai)
                    ->diffInSeconds(\Carbon\Carbon::parse($r->waktu_mulai)));
            });
            $rataDetik  = (int) round($totalDetik / $riwayatDenganWaktu->count());
            $rataRataWaktu = sprintf('%02d:%02d', intdiv($rataDetik, 60), $rataDetik % 60);
        }

        // ── Susun ranking semua peserta ──────────────────────────────────
        $ranked = $allRiwayat
            ->map(function ($r) use ($totalSoal) {
                $jumlahBenar = $r->jawabanPengguna->where('status', 'correct')->count();
                return [
                    'riwayat_id'   => $r->id,
                    'nama_peserta' => $r->nama_peserta ?? 'Anonim',
                    'jumlah_benar' => $jumlahBenar,
                    'total_soal'   => $totalSoal,
                    'durasi'       => $r->durasi,
                    'tanggal'      => $r->created_at?->format('d M Y, H:i'),
                    'total_skor'   => $r->total_skor,
                ];
            })
            // Urutkan: jawaban benar DESC, lalu durasi ASC (lebih cepat = lebih baik)
            ->sortByDesc('jumlah_benar')
            ->values();

        // Podium top 3
        $podium = $ranked->take(3)->values()->map(function ($item, $index) {
            return array_merge($item, ['rank' => $index + 1]);
        });

        // ── Paginasi daftar peserta (dengan search) ──────────────────────
        $paginatedRiwayat = $query->orderByDesc('created_at')->paginate($perPage);

        $daftarPeserta = $paginatedRiwayat->getCollection()->map(function ($r, $index) use ($totalSoal, $paginatedRiwayat) {
            $jumlahBenar = $r->jawabanPengguna->where('status', 'correct')->count();
            return [
                'no'           => (($paginatedRiwayat->currentPage() - 1) * $paginatedRiwayat->perPage()) + $index + 1,
                'riwayat_id'   => $r->id,
                'nama_peserta' => $r->nama_peserta ?? 'Anonim',
                'jumlah_benar' => $jumlahBenar,
                'total_soal'   => $totalSoal,
                'durasi'       => $r->durasi,
                'tanggal'      => $r->created_at?->format('d M Y, H:i'),
                'total_skor'   => $r->total_skor,
            ];
        });

        return response()->json([
            'message' => 'Hasil kuis berhasil diambil.',
            'kuis' => [
                'kuis_id'    => $kuis->kuis_id,
                'judul'      => $kuis->judul,
                'tgl_dibuat' => $kuis->tgl_dibuat,
                'total_soal' => $totalSoal,
            ],
            'statistik' => [
                'total_peserta'   => $totalPeserta,
                'rata_rata_nilai' => $rataRataNilai,
                'waktu_tercepat'  => $waktuTercepat,
                'rata_rata_waktu' => $rataRataWaktu,
            ],
            'podium' => $podium,
            'data'   => $daftarPeserta,
            'pagination' => [
                'total'        => $paginatedRiwayat->total(),
                'per_page'     => $paginatedRiwayat->perPage(),
                'current_page' => $paginatedRiwayat->currentPage(),
                'last_page'    => $paginatedRiwayat->lastPage(),
            ],
        ]);
    }

    /**
     * Detail hasil satu peserta (jawaban per soal).
     *
     * GET /kuis/{kuisId}/hasil/{riwayatId}
     */
    public function show(string $kuisId, string $riwayatId): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuisId);

        if ($kuis->guru_id !== auth('api')->id()) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke hasil kuis ini.',
            ], 403);
        }

        $riwayat = RiwayatKuis::with(['jawabanPengguna.soal'])
            ->where('kuis_id', $kuisId)
            ->findOrFail($riwayatId);

        $totalSoal   = $kuis->countSoal();
        $jumlahBenar = $riwayat->jawabanPengguna->where('status', 'correct')->count();

        $detailJawaban = $riwayat->jawabanPengguna->map(function ($jp) {
            return [
                'soal_id'         => $jp->soal_id,
                'urutan'          => $jp->soal?->urutan,
                'pertanyaan'      => $jp->soal?->soal_soal,
                'jawaban_dipilih' => $jp->jawaban_dipilih,
                'jawaban_benar'   => $jp->soal?->jawaban_benar,
                'status'          => $jp->status, // correct / incorrect / unanswered
            ];
        })->sortBy('urutan')->values();

        return response()->json([
            'message' => 'Detail hasil peserta berhasil diambil.',
            'data' => [
                'riwayat_id'   => $riwayat->id,
                'nama_peserta' => $riwayat->nama_peserta ?? 'Anonim',
                'kuis'         => [
                    'kuis_id' => $kuis->kuis_id,
                    'judul'   => $kuis->judul,
                ],
                'jumlah_benar' => $jumlahBenar,
                'total_soal'   => $totalSoal,
                'total_skor'   => $riwayat->total_skor,
                'durasi'       => $riwayat->durasi,
                'waktu_mulai'  => $riwayat->waktu_mulai,
                'waktu_selesai'=> $riwayat->waktu_selesai,
                'tanggal'      => $riwayat->created_at?->format('d M Y, H:i'),
                'detail_jawaban' => $detailJawaban,
            ],
        ]);
    }

    /**
     * Export CSV semua peserta untuk satu kuis.
     *
     * GET /kuis/{kuisId}/hasil/export
     */
    public function exportCsv(string $kuisId): Response
    {
        $kuis = Kuis::findOrFail($kuisId);

        if ($kuis->guru_id !== auth('api')->id()) {
            abort(403, 'Unauthorized. Anda tidak memiliki akses ke hasil kuis ini.');
        }

        $totalSoal = $kuis->countSoal();

        $riwayatList = RiwayatKuis::with('jawabanPengguna')
            ->where('kuis_id', $kuisId)
            ->where('status', 'completed')
            ->orderByDesc('created_at')
            ->get();

        // Susun ranking
        $ranked = $riwayatList
            ->map(fn($r) => [
                'nama_peserta' => $r->nama_peserta ?? 'Anonim',
                'jumlah_benar' => $r->jawabanPengguna->where('status', 'correct')->count(),
                'total_soal'   => $totalSoal,
                'durasi'       => $r->durasi ?? '-',
                'tanggal'      => $r->created_at?->format('d M Y, H:i') ?? '-',
                'total_skor'   => $r->total_skor,
            ])
            ->sortByDesc('jumlah_benar')
            ->values();

        // Buat isi CSV
        $csvLines = [];
        $csvLines[] = implode(',', [
            'No',
            'Nama Peserta',
            'Jawaban Benar',
            'Total Soal',
            'Waktu Pengerjaan',
            'Tanggal',
            'Total Skor',
        ]);

        foreach ($ranked as $i => $row) {
            $csvLines[] = implode(',', [
                $i + 1,
                '"' . str_replace('"', '""', $row['nama_peserta']) . '"',
                $row['jumlah_benar'],
                $row['total_soal'],
                $row['durasi'],
                '"' . $row['tanggal'] . '"',
                $row['total_skor'],
            ]);
        }

        $csvContent  = implode("\n", $csvLines);
        $fileName    = 'hasil_kuis_' . $kuis->kuis_id . '_' . now()->format('Ymd_His') . '.csv';

        return response($csvContent, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * Submit jawaban peserta setelah mengerjakan kuis.
     * Endpoint ini dipanggil oleh frontend setelah peserta selesai mengerjakan.
     *
     * POST /kuis/{kuisId}/submit
     * Body: { nama_peserta, waktu_mulai, waktu_selesai, jawaban: [{soal_id, jawaban_dipilih}] }
     */
    public function submit(Request $request, string $kuisId): JsonResponse
    {
        $kuis = Kuis::with('soal')->findOrFail($kuisId);

        if (!$kuis->is_published) {
            return response()->json([
                'message' => 'Kuis belum dipublikasikan.',
            ], 403);
        }

        $validated = $request->validate([
            'nama_peserta'              => 'required|string|max:100',
            'waktu_mulai'               => 'required|date',
            'waktu_selesai'             => 'required|date|after:waktu_mulai',
            'jawaban'                   => 'required|array|min:1',
            'jawaban.*.soal_id'         => 'required|integer|exists:soal,id',
            'jawaban.*.jawaban_dipilih' => 'nullable|string|in:a,b,c,d',
        ]);

        // Buat map soal_id → jawaban_benar untuk verifikasi cepat
        $soalMap = $kuis->soal->keyBy('id');

        $totalBenar = 0;
        $totalSkor  = 0;
        $jawabanData = [];

        foreach ($validated['jawaban'] as $jawaban) {
            $soalId        = $jawaban['soal_id'];
            $jawabanDipilih = $jawaban['jawaban_dipilih'] ?? null;
            $soal          = $soalMap->get($soalId);

            if (!$soal) continue;

            if ($jawabanDipilih === null) {
                $status = 'unanswered';
            } elseif (strtolower($jawabanDipilih) === strtolower($soal->jawaban_benar)) {
                $status = 'correct';
                $totalBenar++;
                $totalSkor += $soal->poin;
            } else {
                $status = 'incorrect';
            }

            $jawabanData[] = [
                'soal_id'         => $soalId,
                'jawaban_dipilih' => $jawabanDipilih,
                'status'          => $status,
            ];
        }

        // Simpan riwayat kuis
        $riwayat = RiwayatKuis::create([
            'kuis_id'       => $kuis->kuis_id,
            'nama_peserta'  => $validated['nama_peserta'],
            'waktu_mulai'   => $validated['waktu_mulai'],
            'waktu_selesai' => $validated['waktu_selesai'],
            'total_skor'    => $totalSkor,
            'status'        => 'completed',
        ]);

        // Simpan semua jawaban
        foreach ($jawabanData as $jd) {
            $riwayat->jawabanPengguna()->create($jd);
        }

        $totalSoal = $kuis->countSoal();

        // ── Broadcast event realtime ke guru pemilik kuis ─────────────────
        $durasi = $riwayat->durasi ?? '00:00';
        event(new PesertaSubmitKuis(
            guruId:       $kuis->guru_id,
            kuisId:       $kuis->kuis_id,
            judulKuis:    $kuis->judul,
            namaPeserta:  $validated['nama_peserta'],
            totalSkor:    $totalSkor,
            durasi:       $durasi,
            waktuSelesai: $validated['waktu_selesai'],
        ));

        return response()->json([
            'message' => 'Jawaban berhasil disimpan.',
            'data' => [
                'riwayat_id'   => $riwayat->id,
                'nama_peserta' => $riwayat->nama_peserta,
                'jumlah_benar' => $totalBenar,
                'total_soal'   => $totalSoal,
                'total_skor'   => $totalSkor,
                'durasi'       => $riwayat->durasi,
            ],
        ], 201);
    }
}
