<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKuisRequest;
use App\Models\Kuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KuisController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKuisRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $kuis = Kuis::create([
            'guru_id' => $request->user()->id,
            'judul' => $validated['judul'],
            'kategori' => $validated['kategori'],
            'soal_waktu' => $validated['soal_waktu'],
            'perm_istirahat' => $validated['perm_istirahat'] ?? 0,
            'tgl_dibuat' => $validated['tgl_dibuat'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Kuis berhasil dibuat.',
            'data' => $kuis,
        ], 201);
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
