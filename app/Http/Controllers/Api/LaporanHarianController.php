<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kebun;
use App\Models\LaporanHarian;
use App\Services\GoogleDriveService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanHarianController extends Controller
{
    protected GoogleDriveService $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * GET /api/laporan-harian/today
     */
    public function today(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        $laporan = LaporanHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        return response()->json([
            'success' => true,
            'data' => $laporan,
        ]);
    }

    /**
     * POST /api/laporan-harian
     */
    public function store(Request $request)
    {
        $request->validate([
            'kondisi_tanaman' => 'nullable|string',
            'gulma' => 'nullable|string',
            'hama' => 'nullable|string',
            'penyakit' => 'nullable|string',
            'ajir' => 'nullable|string',
            'perempelan' => 'nullable|string',
            'pemupukan' => 'nullable|string',
            'penyemprotan' => 'nullable|string',
            'kendala' => 'nullable|string',
            'foto_sebelum' => 'nullable',
            'foto_sesudah' => 'nullable',
        ]);

        $user = $request->user();
        $today = Carbon::today();
        $kebun = $user->kebun ?: Kebun::first();

        // Upload foto sebelum ke Google Drive
        $fotoSebelumUrl = null;
        if ($request->hasFile('foto_sebelum')) {
            $res = $this->driveService->uploadFile($request->file('foto_sebelum'), 'laporan_sebelum');
            $fotoSebelumUrl = $res['url'];
        } elseif ($request->filled('foto_sebelum')) {
            $res = $this->driveService->uploadFile($request->foto_sebelum, 'laporan_sebelum');
            $fotoSebelumUrl = $res['url'];
        }

        // Upload foto sesudah ke Google Drive
        $fotoSesudahUrl = null;
        if ($request->hasFile('foto_sesudah')) {
            $res = $this->driveService->uploadFile($request->file('foto_sesudah'), 'laporan_sesudah');
            $fotoSesudahUrl = $res['url'];
        } elseif ($request->filled('foto_sesudah')) {
            $res = $this->driveService->uploadFile($request->foto_sesudah, 'laporan_sesudah');
            $fotoSesudahUrl = $res['url'];
        }

        $laporan = LaporanHarian::updateOrCreate(
            [
                'user_id' => $user->id,
                'tanggal' => $today,
            ],
            [
                'kebun_id' => $kebun ? $kebun->id : null,
                'kondisi_tanaman' => $request->input('kondisi_tanaman', 'Baik'),
                'gulma' => $request->input('gulma', 'Sedang'),
                'hama' => $request->input('hama', 'Ada'),
                'penyakit' => $request->input('penyakit', 'Tidak ada'),
                'ajir' => $request->input('ajir', 'Baik'),
                'perempelan' => $request->input('perempelan', 'Sudah'),
                'pemupukan' => $request->input('pemupukan', 'Dilakukan'),
                'penyemprotan' => $request->input('penyemprotan', 'Tidak'),
                'kendala' => $request->input('kendala', 'Tidak ada'),
                'foto_sebelum_url' => $fotoSebelumUrl ?: 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600',
                'foto_sesudah_url' => $fotoSesudahUrl ?: 'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?w=600',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan harian berhasil dikirim ke sistem dan Google Drive.',
            'data' => $laporan,
        ]);
    }
}
