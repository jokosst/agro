<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\LaporanMasalah;
use App\Services\GoogleDriveService;
use Illuminate\Http\Request;

class LaporanMasalahController extends Controller
{
    protected GoogleDriveService $driveService;

    public function __construct(GoogleDriveService $driveService)
    {
        $this->driveService = $driveService;
    }

    /**
     * GET /api/laporan-masalah
     */
    public function index(Request $request)
    {
        $masalah = LaporanMasalah::with(['user', 'blok'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $masalah,
        ]);
    }

    /**
     * POST /api/laporan-masalah
     */
    public function store(Request $request)
    {
        $request->validate([
            'jenis_masalah' => 'required|string', // Hama, Penyakit, Gulma, dll
            'blok_id' => 'nullable|exists:kebun_bloks,id',
            'baris' => 'nullable|string',
            'jumlah_tanaman' => 'required|integer|min:1',
            'kondisi' => 'required|string',
            'catatan' => 'nullable|string',
            'foto' => 'nullable', // single or multiple
            'fotos.*' => 'nullable|image',
        ]);

        $user = $request->user();
        $kebun = $user->kebun ?: Kebun::first();

        // Process photos to Google Drive
        $fotoUrls = [];
        if ($request->hasFile('fotos')) {
            foreach ($request->file('fotos') as $foto) {
                $res = $this->driveService->uploadFile($foto, 'masalah_tanaman');
                $fotoUrls[] = $res['url'];
            }
        } elseif ($request->hasFile('foto')) {
            $res = $this->driveService->uploadFile($request->file('foto'), 'masalah_tanaman');
            $fotoUrls[] = $res['url'];
        } elseif ($request->filled('foto')) {
            $res = $this->driveService->uploadFile($request->foto, 'masalah_tanaman');
            $fotoUrls[] = $res['url'];
        }

        if (empty($fotoUrls)) {
            $fotoUrls[] = 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600';
        }

        // Cari blok default jika belum dipilih
        $blokId = $request->blok_id;
        if (! $blokId) {
            $firstBlok = KebunBlok::first();
            $blokId = $firstBlok ? $firstBlok->id : null;
        }

        $laporan = LaporanMasalah::create([
            'user_id' => $user->id,
            'kebun_id' => $kebun ? $kebun->id : null,
            'blok_id' => $blokId,
            'baris' => $request->baris ?: 'Blok A - Baris 3',
            'jenis_masalah' => $request->jenis_masalah,
            'jumlah_tanaman' => (int) $request->jumlah_tanaman,
            'kondisi' => $request->kondisi,
            'foto_urls' => $fotoUrls,
            'catatan' => $request->catatan,
            'status' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan masalah tanaman berhasil disimpan ke Google Drive dan database.',
            'data' => $laporan->load('blok'),
        ]);
    }
}
