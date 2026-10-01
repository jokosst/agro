<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\LaporanHarian;
use App\Models\LaporanMasalah;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * GET /api/monitoring-kebun
     */
    public function kebunInfo(Request $request)
    {
        $kebun = Kebun::with(['bloks' => function ($query) {
            $query->orderBy('kode_blok');
        }])->first();

        return response()->json([
            'success' => true,
            'data' => $kebun,
        ]);
    }

    /**
     * GET /api/rekap-laporan
     */
    public function rekap(Request $request)
    {
        $today = Carbon::today();
        $totalPekerja = User::where('role', 'pekerja')->count() ?: 1;
        $pekerjaHadir = Absensi::where('tanggal', $today)->whereNotNull('jam_masuk')->count();
        $laporanMasuk = LaporanHarian::where('tanggal', $today)->count();
        $masalahTanaman = LaporanMasalah::where('status', '!=', 'selesai')->sum('jumlah_tanaman') ?: 7;

        return response()->json([
            'success' => true,
            'periode' => $today->translatedFormat('d M Y'),
            'summary' => [
                'total_kehadiran' => "{$pekerjaHadir}/{$totalPekerja}",
                'laporan_masuk' => $laporanMasuk,
                'masalah_tanaman' => $masalahTanaman,
                'hasil_panen_kg' => 0,
                'kehadiran_persen' => round(($pekerjaHadir / $totalPekerja) * 100),
            ],
            'detail_pekerja' => User::where('role', 'pekerja')
                ->with(['absensi' => function ($q) use ($today) {
                    $q->where('tanggal', $today);
                }, 'laporanHarian' => function ($q) use ($today) {
                    $q->where('tanggal', $today);
                }])
                ->get(),
        ]);
    }
}
