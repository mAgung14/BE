<?php

namespace App\Imports;

use App\Models\Kuis;
use App\Models\Soal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;

class KuisImport implements ToCollection
{
    use Importable;

    public ?Kuis $kuis = null;
    public int $jumlahSoalDiimpor = 0;
    private int $guruId;

    public function __construct(int $guruId)
    {
        $this->guruId = $guruId;
    }

    /**
     * Proses seluruh koleksi baris dari sheet Excel.
     *
     * Mendukung dua format:
     *
     * FORMAT A — Dengan header label (format template resmi):
     *   Baris 1: Label kolom kuis  [JUDUL KUIS, DESKRIPSI, ...]     ← di-skip otomatis
     *   Baris 2: Data kuis         [Ujian Matematika, ..., 60, ...]
     *   Baris 3: Label kolom soal  [PERTANYAAN, POIN, ...]           ← di-skip otomatis
     *   Baris 4+: Data soal        [Pertanyaan soal, 1, A, B, C, D, b]
     *
     * FORMAT B — Langsung data (tanpa header label):
     *   Baris 1: Data kuis         [Judul Kuis, Deskripsi, ...]
     *   Baris 2+: Data soal        [Pertanyaan soal, 1, A, B, C, D, b]
     */
    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            throw new \InvalidArgumentException('File Excel tidak boleh kosong.');
        }

        DB::transaction(function () use ($rows) {
            $allRows = $rows->values(); // re-index agar akses by index aman

            // Cari baris data kuis (baris pertama yang kolom D-nya numerik)
            $kuisRowIndex = $this->findKuisDataRowIndex($allRows);

            if ($kuisRowIndex === null) {
                throw new \InvalidArgumentException(
                    'Tidak ditemukan data kuis yang valid. Pastikan baris data kuis memiliki waktu (kolom D) berupa angka minimal 1.'
                );
            }

            $this->kuis = $this->createKuis($allRows[$kuisRowIndex]->toArray());

            // Baris setelah baris kuis adalah potensi soal (skip baris label soal)
            $soalDimulai = false;
            $urutan = 1;

            for ($i = $kuisRowIndex + 1; $i < $allRows->count(); $i++) {
                $rowArr = $allRows[$i]->toArray();

                // Lewati baris kosong
                if (empty(trim((string) ($rowArr[0] ?? '')))) {
                    continue;
                }

                // Lewati baris label soal (deteksi: kolom G berisi teks "BENAR" atau "JAWABAN BENAR"
                // atau kolom B berisi "POIN" — bukan angka)
                if ($this->isLabelRow($rowArr)) {
                    continue;
                }

                $this->validateSoalRow($rowArr, $i + 1);

                Soal::create([
                    'kuis_id'        => $this->kuis->kuis_id,
                    'soal_soal'      => trim((string) $rowArr[0]),
                    'tipe_soal'      => 'pilihan_ganda',
                    'poin'           => max(1, intval($rowArr[1] ?? 1)),
                    'urutan'         => $urutan,
                    'jawaban_a'      => trim((string) ($rowArr[2] ?? '')),
                    'jawaban_b'      => trim((string) ($rowArr[3] ?? '')),
                    'jawaban_c'      => trim((string) ($rowArr[4] ?? '')),
                    'jawaban_d'      => trim((string) ($rowArr[5] ?? '')),
                    'jawaban_benar'  => strtolower(trim((string) ($rowArr[6] ?? 'a'))),
                ]);

                $urutan++;
                $this->jumlahSoalDiimpor++;
                $soalDimulai = true;
            }

            if ($this->jumlahSoalDiimpor === 0) {
                throw new \InvalidArgumentException('Tidak ada soal valid yang dapat diimpor dari file Excel.');
            }
        });
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Cari index baris yang merupakan data kuis (kolom D harus numerik >= 1).
     * Ini memungkinkan import bekerja meski ada baris label di atas.
     */
    private function findKuisDataRowIndex(Collection $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $rowArr = $row->toArray();

            // Baris kuis: kolom A (judul) tidak kosong DAN kolom D (waktu) adalah angka >= 1
            $judul     = trim((string) ($rowArr[0] ?? ''));
            $soalWaktu = $rowArr[3] ?? null;

            if (!empty($judul) && is_numeric($soalWaktu) && intval($soalWaktu) >= 1) {
                return $index;
            }
        }
        return null;
    }

    /**
     * Deteksi apakah baris adalah baris label/header (bukan data soal).
     * Label row: kolom B (poin) bukan angka, atau kolom G berisi teks "benar".
     */
    private function isLabelRow(array $row): bool
    {
        $kolB = trim((string) ($row[1] ?? ''));
        $kolG = strtolower(trim((string) ($row[6] ?? '')));

        // Jika kolom B bukan angka, ini adalah baris label
        if (!empty($kolB) && !is_numeric($kolB)) {
            return true;
        }

        // Jika kolom G berisi kata "benar" (misal "JAWABAN BENAR (a/b/c/d)"), ini label
        if (str_contains($kolG, 'benar') || str_contains($kolG, 'jawaban')) {
            return true;
        }

        return false;
    }

    private function createKuis(array $row): Kuis
    {
        $judul = trim((string) ($row[0] ?? ''));
        if (empty($judul)) {
            throw new \InvalidArgumentException('Judul kuis (kolom A) tidak boleh kosong.');
        }

        $akses    = strtolower(trim((string) ($row[5] ?? 'private')));
        $akses    = in_array($akses, ['publik', 'private']) ? $akses : 'private';
        $kodeKuis = ($akses === 'publik') ? null : Kuis::generateKodeKuis();

        $soalWaktu = intval($row[3] ?? 60);
        if ($soalWaktu < 1) {
            throw new \InvalidArgumentException('Waktu soal (kolom D) harus minimal 1 menit.');
        }

        return Kuis::create([
            'guru_id'        => $this->guruId,
            'judul'          => $judul,
            'deskripsi'      => trim((string) ($row[1] ?? '')),
            'kategori'       => trim((string) ($row[2] ?? 'Umum')),
            'soal_waktu'     => $soalWaktu,
            'perm_istirahat' => max(0, intval($row[4] ?? 0)),
            'tgl_dibuat'     => now(),
            'akses'          => $akses,
            'kode_kuis'      => $kodeKuis,
            'status'         => 'draft',
            'is_published'   => false,
        ]);
    }

    private function validateSoalRow(array $row, int $lineNumber): void
    {
        $pertanyaan = trim((string) ($row[0] ?? ''));
        if (empty($pertanyaan)) {
            throw new \InvalidArgumentException("Baris {$lineNumber}: Kolom pertanyaan (A) tidak boleh kosong.");
        }

        $jawabanBenar = strtolower(trim((string) ($row[6] ?? '')));
        if (!in_array($jawabanBenar, ['a', 'b', 'c', 'd'])) {
            throw new \InvalidArgumentException(
                "Baris {$lineNumber}: Kolom jawaban benar (G) harus berisi a, b, c, atau d. Nilai saat ini: '{$jawabanBenar}'"
            );
        }

        $jawabanMap   = ['a' => 2, 'b' => 3, 'c' => 4, 'd' => 5];
        $colIndex     = $jawabanMap[$jawabanBenar];
        $nilaiJawaban = trim((string) ($row[$colIndex] ?? ''));

        if (empty($nilaiJawaban)) {
            $kolom = strtoupper($jawabanBenar);
            throw new \InvalidArgumentException(
                "Baris {$lineNumber}: Jawaban pilihan {$kolom} yang ditandai sebagai benar tidak boleh kosong."
            );
        }
    }
}