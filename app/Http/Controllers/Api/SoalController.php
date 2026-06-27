<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSoalRequest;
use App\Http\Requests\UpdateSoalRequest;
use App\Models\Soal;
use App\Models\Kuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoalController extends Controller
{
    /**
     * Display all questions for a quiz
     */
    public function index(string $kuisId): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuisId);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        $soal = $kuis->soal()->orderBy('urutan')->get();

        return response()->json([
            'message' => 'Data soal berhasil diambil.',
            'data' => $soal,
        ]);
    }

    /**
     * Store a newly created question
     */
    public function store(StoreSoalRequest $request): JsonResponse
    {
        $kuis = Kuis::findOrFail($request->kuis_id);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        $validated = $request->validated();

        // Get next urutan
        $nextUrutan = Soal::where('kuis_id', $kuis->kuis_id)->max('urutan') + 1 ?? 1;

        // Handle image uploads
        $gambarSoal = null;
        $gambarJawabanA = null;
        $gambarJawabanB = null;
        $gambarJawabanC = null;
        $gambarJawabanD = null;

        if ($request->hasFile('gambar_soal')) {
            $gambarSoal = $request->file('gambar_soal')->store('soal', 'public');
        }
        if ($request->hasFile('gambar_jawaban_a')) {
            $gambarJawabanA = $request->file('gambar_jawaban_a')->store('jawaban', 'public');
        }
        if ($request->hasFile('gambar_jawaban_b')) {
            $gambarJawabanB = $request->file('gambar_jawaban_b')->store('jawaban', 'public');
        }
        if ($request->hasFile('gambar_jawaban_c')) {
            $gambarJawabanC = $request->file('gambar_jawaban_c')->store('jawaban', 'public');
        }
        if ($request->hasFile('gambar_jawaban_d')) {
            $gambarJawabanD = $request->file('gambar_jawaban_d')->store('jawaban', 'public');
        }

        $soal = Soal::create([
            'kuis_id' => $kuis->kuis_id,
            'soal_soal' => $validated['soal_soal'],
            'gambar_soal' => $gambarSoal,
            'tipe_soal' => $validated['tipe_soal'] ?? 'pilihan_ganda',
            'poin' => $validated['poin'] ?? 1,
            'urutan' => $nextUrutan,
            'jawaban_a' => $validated['jawaban_a'],
            'jawaban_b' => $validated['jawaban_b'],
            'jawaban_c' => $validated['jawaban_c'],
            'jawaban_d' => $validated['jawaban_d'],
            'jawaban_benar' => strtolower($validated['jawaban_benar']),
            'gambar_jawaban_a' => $gambarJawabanA,
            'gambar_jawaban_b' => $gambarJawabanB,
            'gambar_jawaban_c' => $gambarJawabanC,
            'gambar_jawaban_d' => $gambarJawabanD,
        ]);

        return response()->json([
            'message' => 'Soal berhasil ditambahkan.',
            'data' => $soal,
        ], 201);
    }

    /**
     * Display the specified question
     */
    public function show(string $id): JsonResponse
    {
        $soal = Soal::findOrFail($id);

        // Check authorization
        if ($soal->kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke soal ini.',
            ], 403);
        }

        return response()->json([
            'message' => 'Detail soal berhasil diambil.',
            'data' => $soal,
        ]);
    }

    /**
     * Update the specified question
     */
    public function update(UpdateSoalRequest $request, string $id): JsonResponse
    {
        $soal = Soal::findOrFail($id);

        // Check authorization
        if ($soal->kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke soal ini.',
            ], 403);
        }

        $validated = $request->validated();

        // Handle image uploads
        if ($request->hasFile('gambar_soal')) {
            if ($soal->gambar_soal) {
                \Storage::disk('public')->delete($soal->gambar_soal);
            }
            $soal->gambar_soal = $request->file('gambar_soal')->store('soal', 'public');
        }

        if ($request->hasFile('gambar_jawaban_a')) {
            if ($soal->gambar_jawaban_a) {
                \Storage::disk('public')->delete($soal->gambar_jawaban_a);
            }
            $soal->gambar_jawaban_a = $request->file('gambar_jawaban_a')->store('jawaban', 'public');
        }

        if ($request->hasFile('gambar_jawaban_b')) {
            if ($soal->gambar_jawaban_b) {
                \Storage::disk('public')->delete($soal->gambar_jawaban_b);
            }
            $soal->gambar_jawaban_b = $request->file('gambar_jawaban_b')->store('jawaban', 'public');
        }

        if ($request->hasFile('gambar_jawaban_c')) {
            if ($soal->gambar_jawaban_c) {
                \Storage::disk('public')->delete($soal->gambar_jawaban_c);
            }
            $soal->gambar_jawaban_c = $request->file('gambar_jawaban_c')->store('jawaban', 'public');
        }

        if ($request->hasFile('gambar_jawaban_d')) {
            if ($soal->gambar_jawaban_d) {
                \Storage::disk('public')->delete($soal->gambar_jawaban_d);
            }
            $soal->gambar_jawaban_d = $request->file('gambar_jawaban_d')->store('jawaban', 'public');
        }

        $soal->update([
            'soal_soal'        => $validated['soal_soal'] ?? $soal->soal_soal,
            'poin'             => $validated['poin'] ?? $soal->poin,
            'jawaban_a'        => $validated['jawaban_a'] ?? $soal->jawaban_a,
            'jawaban_b'        => $validated['jawaban_b'] ?? $soal->jawaban_b,
            'jawaban_c'        => $validated['jawaban_c'] ?? $soal->jawaban_c,
            'jawaban_d'        => $validated['jawaban_d'] ?? $soal->jawaban_d,
            'jawaban_benar'    => strtolower($validated['jawaban_benar'] ?? $soal->jawaban_benar),
            // Gambar — nilai sudah diperbarui di property $soal di atas (jika ada file baru)
            'gambar_soal'      => $soal->gambar_soal,
            'gambar_jawaban_a' => $soal->gambar_jawaban_a,
            'gambar_jawaban_b' => $soal->gambar_jawaban_b,
            'gambar_jawaban_c' => $soal->gambar_jawaban_c,
            'gambar_jawaban_d' => $soal->gambar_jawaban_d,
        ]);

        return response()->json([
            'message' => 'Soal berhasil diperbarui.',
            'data' => $soal,
        ]);
    }

    /**
     * Remove the specified question
     */
    public function destroy(string $id): JsonResponse
    {
        $soal = Soal::findOrFail($id);

        // Check authorization
        if ($soal->kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke soal ini.',
            ], 403);
        }

        // Delete images
        if ($soal->gambar_soal) {
            \Storage::disk('public')->delete($soal->gambar_soal);
        }
        if ($soal->gambar_jawaban_a) {
            \Storage::disk('public')->delete($soal->gambar_jawaban_a);
        }
        if ($soal->gambar_jawaban_b) {
            \Storage::disk('public')->delete($soal->gambar_jawaban_b);
        }
        if ($soal->gambar_jawaban_c) {
            \Storage::disk('public')->delete($soal->gambar_jawaban_c);
        }
        if ($soal->gambar_jawaban_d) {
            \Storage::disk('public')->delete($soal->gambar_jawaban_d);
        }

        $soal->delete();

        return response()->json([
            'message' => 'Soal berhasil dihapus.',
        ]);
    }

    /**
     * Reorder questions
     */
    public function reorder(Request $request, string $kuisId): JsonResponse
    {
        $kuis = Kuis::findOrFail($kuisId);

        // Check authorization
        if ($kuis->guru_id !== auth()->user()->id) {
            return response()->json([
                'message' => 'Unauthorized. Anda tidak memiliki akses ke kuis ini.',
            ], 403);
        }

        $validated = $request->validate([
            'soal_ids' => 'required|array',
            'soal_ids.*' => 'required|integer',
        ]);

        foreach ($validated['soal_ids'] as $urutan => $soalId) {
            Soal::where('id', $soalId)
                ->where('kuis_id', $kuisId)
                ->update(['urutan' => $urutan + 1]);
        }

        return response()->json([
            'message' => 'Urutan soal berhasil diperbarui.',
        ]);
    }
}
