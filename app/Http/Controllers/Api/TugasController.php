<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kebun;
use App\Models\PekerjaTugas;
use App\Models\TugasHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TugasController extends Controller
{
    /**
     * GET /api/tugas
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $kebun = $user->kebun ?: Kebun::first();

        $tugasList = TugasHarian::where('is_active', true)
            ->orderBy('urutan')
            ->get();

        // Ambil centang user hari ini
        $pekerjaTugas = PekerjaTugas::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->pluck('is_completed', 'tugas_harian_id')
            ->toArray();

        $result = $tugasList->map(function ($item) use ($pekerjaTugas) {
            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'deskripsi' => $item->deskripsi,
                'urutan' => $item->urutan,
                'is_completed' => (bool) ($pekerjaTugas[$item->id] ?? false),
            ];
        });

        $total = $result->count();
        $completed = $result->where('is_completed', true)->count();

        return response()->json([
            'success' => true,
            'tanggal' => $today->translatedFormat('d F Y'),
            'total' => $total,
            'completed' => $completed,
            'progress_percent' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'data' => $result,
        ]);
    }

    /**
     * POST /api/tugas/toggle/{id}
     */
    public function toggle(Request $request, $id)
    {
        $user = $request->user();
        $today = Carbon::today();

        $tugas = TugasHarian::findOrFail($id);

        $record = PekerjaTugas::firstOrNew([
            'user_id' => $user->id,
            'tugas_harian_id' => $tugas->id,
            'tanggal' => $today,
        ]);

        $newStatus = ! $record->is_completed;
        $record->is_completed = $newStatus;
        $record->completed_at = $newStatus ? Carbon::now() : null;
        $record->save();

        return response()->json([
            'success' => true,
            'message' => $newStatus ? 'Tugas ditandai selesai.' : 'Tugas dibatalkan selesai.',
            'data' => [
                'id' => $tugas->id,
                'judul' => $tugas->judul,
                'is_completed' => $newStatus,
            ],
        ]);
    }
}
