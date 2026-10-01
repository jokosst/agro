<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\LaporanMasalah;
use App\Models\MasterHamaPenyakit;
use App\Models\PekerjaTugas;
use App\Models\TugasHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MasterApiController extends Controller
{
    /**
     * GET /api/master/lahan
     * Mengambil data kebun utama dan seluruh blok lahan
     */
    public function lahan(Request $request)
    {
        $kebun = Kebun::with(['bloks' => function ($query) {
            $query->orderBy('kode_blok')->with(['laporanMasalah' => function ($q) {
                $q->whereNotNull('foto_urls')
                    ->where('foto_urls', '!=', '[]')
                    ->latest()
                    ->limit(1);
            }]);
        }])->first();

        if ($kebun && $kebun->bloks) {
            $kebun->bloks->each(function ($blok) {
                $latestFoto = $blok->laporanMasalah?->first();
                $fotoUrls = [];
                if ($latestFoto && $latestFoto->foto_urls) {
                    $rawFoto = $latestFoto->foto_urls;
                    $decoded = is_array($rawFoto) ? $rawFoto : json_decode($rawFoto, true);
                    $fotoUrls = is_array($decoded) ? array_slice($decoded, 0, 1) : [];
                }
                $blok->foto_kondisi = $fotoUrls[0] ?? null;
                $blok->foto_kondisi_label = $latestFoto?->jenis_masalah ?? null;
            });
        }

        $googleMapsApiKey = config('services.google.maps_api_key', env('GOOGLE_MAPS_API_KEY', ''));

        return response()->json([
            'success' => true,
            'data' => $kebun,
            'google_maps_api_key' => $googleMapsApiKey,
        ]);
    }

    /**
     * GET /api/master/data-dukung
     * Mengambil data master hama, penyakit, dan gulma
     */
    public function dataDukung(Request $request)
    {
        $data = MasterHamaPenyakit::where('status', 'aktif')
            ->orderBy('kategori')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * GET /api/master/pekerja
     * Mengambil daftar pekerja kebun
     */
    public function pekerja(Request $request)
    {
        $today = Carbon::today();
        $pekerja = User::where('role', 'pekerja')
            ->with(['absensi' => function ($q) use ($today) {
                $q->where('tanggal', $today);
            }])
            ->get()
            ->map(function ($p) {
                $todayAbsen = $p->absensi->first();

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'username' => $p->username,
                    'email' => $p->email,
                    'phone' => $p->phone ?: '-',
                    'role' => $p->role,
                    'jabatan' => $p->jabatan ?: 'Pekerja Kebun',
                    'sudah_hadir' => $todayAbsen && $todayAbsen->jam_masuk != null,
                    'jam_masuk' => $todayAbsen ? $todayAbsen->jam_masuk : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pekerja,
        ]);
    }

    /**
     * GET /api/notifikasi
     * Mengambil daftar notifikasi dan pengingat aktif untuk pekerja
     */
    public function notifikasi(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();
        $notifications = [];

        // 1. Cek status absensi hari ini
        $absen = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        if (! $absen || ! $absen->jam_masuk) {
            $notifications[] = [
                'id' => 'absen_masuk',
                'type' => 'warning',
                'title' => 'Absen Masuk Belum Dilakukan',
                'message' => 'Silakan lakukan selfie dan validasi lokasi GPS kebun sebelum bekerja.',
                'time' => 'Hari ini',
                'is_urgent' => true,
                'action' => 'absen_masuk',
            ];
        } else {
            $notifications[] = [
                'id' => 'absen_ok',
                'type' => 'success',
                'title' => 'Absen Pagi Tercatat',
                'message' => 'Anda telah berhasil hadir pada pukul '.substr($absen->jam_masuk, 0, 5).' WIB.',
                'time' => 'Hari ini',
                'is_urgent' => false,
                'action' => 'none',
            ];
        }

        // 2. Cek tugas harian yang belum selesai
        $totalTugas = TugasHarian::where('is_active', true)->count();
        $selesai = PekerjaTugas::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->where('is_completed', true)
            ->count();
        $sisa = $totalTugas - $selesai;

        if ($sisa > 0) {
            $notifications[] = [
                'id' => 'tugas_sisa',
                'type' => 'info',
                'title' => "$sisa Tugas Harian Belum Selesai",
                'message' => "Ada $sisa dari $totalTugas tugas pemeliharaan cabai yang masih perlu diselesaikan hari ini.",
                'time' => 'Hari ini',
                'is_urgent' => false,
                'action' => 'tugas',
            ];
        }

        // 3. Cek peringatan masalah & hama tanaman terbaru di kebun
        $masalahAktif = LaporanMasalah::where('status', '!=', 'selesai')
            ->latest()
            ->take(3)
            ->get();

        foreach ($masalahAktif as $m) {
            $lokasi = $m->baris ?: 'Kebun Cabai';
            $notifications[] = [
                'id' => 'masalah_'.$m->id,
                'type' => 'danger',
                'title' => "Perhatian: {$m->jenis_masalah} di $lokasi",
                'message' => "Ditemukan {$m->jumlah_tanaman} tanaman terkena {$m->kondisi}. Segera periksa.",
                'time' => $m->created_at->diffForHumans(),
                'is_urgent' => true,
                'action' => 'monitoring',
            ];
        }

        return response()->json([
            'success' => true,
            'unread_count' => count(array_filter($notifications, fn ($n) => $n['is_urgent'])),
            'data' => $notifications,
        ]);
    }
}
