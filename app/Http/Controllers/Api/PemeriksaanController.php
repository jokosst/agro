<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kebun;
use App\Models\PemeriksaanTanaman;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PemeriksaanController extends Controller
{
    protected GoogleDriveService $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * POST /api/pemeriksaan
     */
    public function store(Request $request)
    {
        $request->validate([
            'kondisi_daun' => 'required|string',
            'kondisi_batang' => 'required|string',
            'kondisi_bunga' => 'required|string',
            'kondisi_buah' => 'required|string',
            'foto' => 'nullable',
            'catatan' => 'nullable|string',
        ]);

        $user = $request->user();
        $kebun = $user->kebun ?: Kebun::first();

        $fotoUrl = null;
        if ($request->hasFile('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->file('foto'), 'pemeriksaan_tanaman');
            $fotoUrl = $uploadResult['url'];
        } elseif ($request->filled('foto')) {
            $uploadResult = $this->driveService->uploadFile($request->foto, 'pemeriksaan_tanaman');
            $fotoUrl = $uploadResult['url'];
        }

        $pemeriksaan = PemeriksaanTanaman::create([
            'user_id' => $user->id,
            'kebun_id' => $kebun ? $kebun->id : null,
            'tanggal' => Carbon::today(),
            'kondisi_daun' => $request->kondisi_daun,
            'kondisi_batang' => $request->kondisi_batang,
            'kondisi_bunga' => $request->kondisi_bunga,
            'kondisi_buah' => $request->kondisi_buah,
            'foto_url' => $fotoUrl ?: 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600',
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hasil pemeriksaan tanaman berhasil disimpan ke sistem dan Google Drive.',
            'data' => $pemeriksaan,
        ]);
    }

    /**
     * GET /api/pemeriksaan/today
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $data = PemeriksaanTanaman::where('user_id', $user->id)
            ->where('tanggal', Carbon::today())
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
