<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Kebun;
use App\Models\KebunBlok;
use App\Models\LaporanHarian;
use App\Models\LaporanMasalah;
use App\Models\PekerjaTugas;
use App\Models\PemeriksaanTanaman;
use App\Models\TugasHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Dashboard Utama Admin dengan Real KPI & Data Grafik
     */
    public function dashboard()
    {
        $today = Carbon::today();
        $kebun = Kebun::first();
        $totalPekerja = User::where('role', 'pekerja')->count() ?: 1;
        $pekerjaHadir = Absensi::where('tanggal', $today)->whereNotNull('jam_masuk')->count();
        $laporanHariIni = LaporanHarian::where('tanggal', $today)->count();
        $masalahTanaman = LaporanMasalah::where('status', '!=', 'selesai')->sum('jumlah_tanaman');
        $totalHama = KebunBlok::sum('jumlah_hama');

        $absensiHariIni = Absensi::with('user')->where('tanggal', $today)->latest()->get();
        $masalahTerbaru = LaporanMasalah::with(['user', 'blok'])->latest()->take(5)->get();
        $bloks = KebunBlok::all();

        // 1. Data Grafik Tren Kehadiran 7 Hari Terakhir
        $chartDates = [];
        $chartHadir = [];
        $chartTidakHadir = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $chartDates[] = $d->translatedFormat('D, d M');
            $hadirCount = Absensi::where('tanggal', $d->toDateString())->whereNotNull('jam_masuk')->count();
            $chartHadir[] = $hadirCount;
            $chartTidakHadir[] = max(0, $totalPekerja - $hadirCount);
        }

        // 2. Data Grafik Masalah Tanaman (Berdasarkan Jenis)
        $masalahGroup = LaporanMasalah::selectRaw('jenis_masalah, count(*) as count')
            ->groupBy('jenis_masalah')
            ->pluck('count', 'jenis_masalah')
            ->toArray();

        $chartJenisLabels = ! empty($masalahGroup) ? array_keys($masalahGroup) : ['Hama', 'Penyakit', 'Gulma', 'Fisiologis'];
        $chartJenisData = ! empty($masalahGroup) ? array_values($masalahGroup) : [5, 2, 1, 0];

        // 3. Data Distribusi Tanaman Sakit per Blok
        $chartBlokLabels = [];
        $chartBlokData = [];
        foreach ($bloks as $b) {
            $chartBlokLabels[] = $b->kode_blok;
            $chartBlokData[] = $b->jumlah_masalah ?: ($b->laporanMasalah()->where('status', '!=', 'selesai')->sum('jumlah_tanaman') ?: 0);
        }

        return view('admin.dashboard', compact(
            'kebun',
            'totalPekerja',
            'pekerjaHadir',
            'laporanHariIni',
            'masalahTanaman',
            'totalHama',
            'absensiHariIni',
            'masalahTerbaru',
            'bloks',
            'chartDates',
            'chartHadir',
            'chartTidakHadir',
            'chartJenisLabels',
            'chartJenisData',
            'chartBlokLabels',
            'chartBlokData'
        ));
    }

    /**
     * Monitoring Peta Interaktif (Google Maps / Leaflet Satellite)
     */
    public function monitoringPeta()
    {
        $kebun = Kebun::first();
        if (! $kebun) {
            $kebun = Kebun::create([
                'nama' => 'Kebun Cabai Agrocom',
                'lokasi_text' => 'Sambas, Kalimantan Barat',
                'latitude' => -0.1234000,
                'longitude' => 109.3456000,
                'radius_meter' => 50,
                'luas_lahan' => '2 Hektar',
                'status' => 'aktif',
            ]);
        }

        $bloks = KebunBlok::where('kebun_id', $kebun->id)->get();
        $googleMapsApiKey = config('services.google.maps_api_key', env('GOOGLE_MAPS_API_KEY', ''));

        return view('admin.monitoring_peta', compact('kebun', 'bloks', 'googleMapsApiKey'));
    }

    /**
     * Rekap Absensi Pekerja
     */
    public function absensi(Request $request)
    {
        $query = Absensi::with('user')->latest('tanggal');

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->filled('pekerja_id')) {
            $query->where('user_id', $request->pekerja_id);
        }

        $absensiList = $query->paginate(15)->withQueryString();
        $pekerjaList = User::where('role', 'pekerja')->get();

        return view('admin.absensi', compact('absensiList', 'pekerjaList'));
    }

    /**
     * Laporan Masalah & Hama Kebun Dinamis
     */
    public function laporanMasalah(Request $request)
    {
        $query = LaporanMasalah::with(['user', 'blok'])->latest('created_at');

        if ($request->filled('jenis') && $request->jenis !== 'semua') {
            $query->where('jenis_masalah', $request->jenis);
        }
        if ($request->filled('status') && $request->status !== 'semua') {
            $query->where('status', $request->status);
        }
        if ($request->filled('blok_id') && $request->blok_id !== 'semua') {
            $query->where('blok_id', $request->blok_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('kondisi', 'like', "%{$s}%")
                    ->orWhere('catatan', 'like', "%{$s}%")
                    ->orWhereHas('user', function ($uq) use ($s) {
                        $uq->where('name', 'like', "%{$s}%");
                    });
            });
        }

        $masalahList = $query->paginate(15)->withQueryString();

        // Metrics Real
        $totalTemuan = LaporanMasalah::count();
        $menungguCount = LaporanMasalah::where('status', 'menunggu')->count();
        $ditanganiCount = LaporanMasalah::where('status', 'ditangani')->count();
        $selesaiCount = LaporanMasalah::where('status', 'selesai')->count();
        $totalPohonSakit = LaporanMasalah::where('status', '!=', 'selesai')->sum('jumlah_tanaman');

        $bloks = KebunBlok::all();

        return view('admin.laporan_masalah', compact(
            'masalahList',
            'totalTemuan',
            'menungguCount',
            'ditanganiCount',
            'selesaiCount',
            'totalPohonSakit',
            'bloks'
        ));
    }

    /**
     * Update Status Laporan Masalah
     */
    public function updateStatusMasalah(Request $request, $id)
    {
        $masalah = LaporanMasalah::findOrFail($id);
        $status = $request->input('status', 'selesai');
        $masalah->status = $status;
        $masalah->save();

        return back()->with('success', 'Status laporan temuan berhasil diubah menjadi: '.ucfirst($status));
    }

    /**
     * Rekap Laporan Harian, Mingguan, Bulanan (Tabel & Kartu + Filter Periode Aktif)
     */
    public function laporanHarian(Request $request)
    {
        $period = $request->input('period', 'harian'); // harian | mingguan | bulanan
        $viewMode = $request->input('view', 'table'); // table | cards
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        $query = LaporanHarian::with('user');

        $carbonDate = Carbon::parse($selectedDate);

        if ($period === 'harian') {
            $query->whereDate('tanggal', $selectedDate);
            $periodeText = $carbonDate->translatedFormat('d F Y');
        } elseif ($period === 'mingguan') {
            $startOfWeek = $carbonDate->copy()->startOfWeek();
            $endOfWeek = $carbonDate->copy()->endOfWeek();
            $query->whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
            $periodeText = $startOfWeek->translatedFormat('d M').' - '.$endOfWeek->translatedFormat('d M Y');
        } elseif ($period === 'bulanan') {
            $query->whereYear('tanggal', $carbonDate->year)
                ->whereMonth('tanggal', $carbonDate->month);
            $periodeText = $carbonDate->translatedFormat('F Y');
        } else {
            $periodeText = 'Semua Data';
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $laporanList = $query->latest('tanggal')->paginate(15)->withQueryString();

        // Real KPIs for the selected period
        $reportIds = (clone $query)->pluck('id');
        $userIds = (clone $query)->pluck('user_id')->unique();
        $totalLaporan = $reportIds->count();
        $totalHadir = Absensi::whereIn('user_id', $userIds)
            ->when($period === 'harian', fn ($q) => $q->whereDate('tanggal', $selectedDate))
            ->when($period === 'mingguan', fn ($q) => $q->whereBetween('tanggal', [$carbonDate->copy()->startOfWeek()->toDateString(), $carbonDate->copy()->endOfWeek()->toDateString()]))
            ->when($period === 'bulanan', fn ($q) => $q->whereYear('tanggal', $carbonDate->year)->whereMonth('tanggal', $carbonDate->month))
            ->whereNotNull('jam_masuk')
            ->count();

        $totalPekerjaAktif = User::where('role', 'pekerja')->count() ?: 1;

        $masalahPeriode = LaporanMasalah::when($period === 'harian', fn ($q) => $q->whereDate('created_at', $selectedDate))
            ->when($period === 'mingguan', fn ($q) => $q->whereBetween('created_at', [$carbonDate->copy()->startOfWeek()->startOfDay(), $carbonDate->copy()->endOfWeek()->endOfDay()]))
            ->when($period === 'bulanan', fn ($q) => $q->whereYear('created_at', $carbonDate->year)->whereMonth('created_at', $carbonDate->month))
            ->sum('jumlah_tanaman');

        $pupukCount = (clone $query)->where('pemupukan', 'Dilakukan')->count();
        $semprotCount = (clone $query)->where('penyemprotan', 'Dilakukan')->count();

        // Chart Data for Rekap Period
        $chartLabels = [];
        $chartReportsCount = [];
        if ($period === 'mingguan') {
            for ($i = 0; $i < 7; $i++) {
                $cur = $carbonDate->copy()->startOfWeek()->addDays($i);
                $chartLabels[] = $cur->translatedFormat('D, d M');
                $chartReportsCount[] = LaporanHarian::whereDate('tanggal', $cur->toDateString())->count();
            }
        } elseif ($period === 'bulanan') {
            $daysInMonth = $carbonDate->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d += 3) {
                $cur = Carbon::create($carbonDate->year, $carbonDate->month, min($d, $daysInMonth));
                $chartLabels[] = $cur->format('d/m');
                $chartReportsCount[] = LaporanHarian::whereYear('tanggal', $carbonDate->year)
                    ->whereMonth('tanggal', $carbonDate->month)
                    ->whereDay('tanggal', $cur->day)
                    ->count();
            }
        } else {
            // Harian: 7 days comparison
            for ($i = 6; $i >= 0; $i--) {
                $cur = Carbon::parse($selectedDate)->subDays($i);
                $chartLabels[] = $cur->translatedFormat('d M');
                $chartReportsCount[] = LaporanHarian::whereDate('tanggal', $cur->toDateString())->count();
            }
        }

        $pekerjaList = User::where('role', 'pekerja')->get();

        return view('admin.laporan_harian', compact(
            'laporanList',
            'period',
            'viewMode',
            'selectedDate',
            'periodeText',
            'totalLaporan',
            'totalHadir',
            'totalPekerjaAktif',
            'masalahPeriode',
            'pupukCount',
            'semprotCount',
            'pekerjaList',
            'chartLabels',
            'chartReportsCount'
        ));
    }

    /**
     * Detail Laporan Kerja Harian Pekerja (Disesuaikan Penuh dengan Data Sebenarnya)
     */
    public function detailPekerja($id, Request $request)
    {
        $pekerja = User::with('kebun')->findOrFail($id);

        // Cari tanggal laporan: jika diberikan di request gunakan itu, jika tidak cari tanggal absensi/laporan terbaru
        $selectedDate = $request->input('date');
        if (! $selectedDate) {
            $latestAbsen = Absensi::where('user_id', $id)->latest('tanggal')->first();
            $latestLaporan = LaporanHarian::where('user_id', $id)->latest('tanggal')->first();
            $selectedDate = $latestAbsen?->tanggal ?? ($latestLaporan?->tanggal ?? Carbon::today()->toDateString());
        }

        // Data Absensi
        $absensi = Absensi::where('user_id', $id)->whereDate('tanggal', $selectedDate)->first();

        // Data Checklist Tugas Harian: ambil semua master tugas aktif dan cek status penyelesaiannya
        $masterTugas = TugasHarian::where('is_active', true)->orderBy('urutan', 'asc')->get();
        $userTugasCompleted = PekerjaTugas::where('user_id', $id)
            ->whereDate('tanggal', $selectedDate)
            ->pluck('is_completed', 'tugas_harian_id')
            ->toArray();

        $tugasList = $masterTugas->map(function ($task) use ($userTugasCompleted) {
            $task->is_completed = ! empty($userTugasCompleted[$task->id]);

            return $task;
        });

        // Data Laporan Harian
        $laporanHarian = LaporanHarian::where('user_id', $id)->whereDate('tanggal', $selectedDate)->first();

        // Data Pemeriksaan Tanaman
        $pemeriksaan = PemeriksaanTanaman::where('user_id', $id)->whereDate('tanggal', $selectedDate)->first();

        // Data Laporan Masalah / Hama
        $masalahList = LaporanMasalah::with('blok')
            ->where('user_id', $id)
            ->where(function ($q) use ($selectedDate) {
                $q->whereDate('created_at', $selectedDate)
                    ->orWhere('status', '!=', 'selesai');
            })
            ->latest()
            ->get();

        // Hitung real count masalah & hama
        $masalahCount = $masalahList->where('jenis_masalah', '!=', 'Hama')->sum('jumlah_tanaman') ?: $masalahList->count();
        $hamaCount = $masalahList->where('jenis_masalah', 'Hama')->sum('jumlah_tanaman');

        // Kumpulkan Semua Foto Dokumentasi Nyata (Google Drive / Storage)
        $fotoKoleksi = [];

        if ($absensi && ! empty($absensi->foto_masuk)) {
            $fotoKoleksi[] = [
                'url' => $absensi->foto_masuk,
                'title' => 'Selfie Absen Masuk',
                'time' => $absensi->jam_masuk ?? '-',
                'badge' => 'Absen Masuk',
            ];
        }

        if ($absensi && ! empty($absensi->foto_pulang)) {
            $fotoKoleksi[] = [
                'url' => $absensi->foto_pulang,
                'title' => 'Selfie Absen Pulang',
                'time' => $absensi->jam_pulang ?? '-',
                'badge' => 'Absen Pulang',
            ];
        }

        if ($laporanHarian && ! empty($laporanHarian->foto_sebelum_url)) {
            $fotoKoleksi[] = [
                'url' => $laporanHarian->foto_sebelum_url,
                'title' => 'Kondisi Kebun Sebelum Kerja',
                'time' => 'Laporan Harian',
                'badge' => 'Foto Sebelum',
            ];
        }

        if ($laporanHarian && ! empty($laporanHarian->foto_sesudah_url)) {
            $fotoKoleksi[] = [
                'url' => $laporanHarian->foto_sesudah_url,
                'title' => 'Kondisi Kebun Sesudah Kerja',
                'time' => 'Laporan Harian',
                'badge' => 'Foto Sesudah',
            ];
        }

        if ($pemeriksaan && ! empty($pemeriksaan->foto_url)) {
            $fotoKoleksi[] = [
                'url' => $pemeriksaan->foto_url,
                'title' => 'Foto Pemeriksaan Fisik Tanaman',
                'time' => 'Cek Tanaman',
                'badge' => 'Pemeriksaan',
            ];
        }

        foreach ($masalahList as $m) {
            $fotos = is_array($m->foto_urls) ? $m->foto_urls : json_decode($m->foto_urls, true);
            if (! empty($fotos) && is_array($fotos)) {
                foreach ($fotos as $f) {
                    $fotoKoleksi[] = [
                        'url' => $f,
                        'title' => 'Temuan: '.$m->jenis_masalah.' ('.($m->blok->kode_blok ?? 'Kebun').')',
                        'time' => $m->created_at->format('H:i'),
                        'badge' => $m->jenis_masalah,
                    ];
                }
            }
        }

        // Daftar tanggal yang pernah dilaporkan pekerja ini untuk filter dropdown
        $allDates = Absensi::where('user_id', $id)->pluck('tanggal')->map(fn ($d) => Carbon::parse($d)->toDateString())
            ->merge(LaporanHarian::where('user_id', $id)->pluck('tanggal')->map(fn ($d) => Carbon::parse($d)->toDateString()))
            ->unique()
            ->sortDesc()
            ->values();

        return view('admin.detail_pekerja', compact(
            'pekerja',
            'selectedDate',
            'absensi',
            'tugasList',
            'laporanHarian',
            'masalahList',
            'masalahCount',
            'hamaCount',
            'pemeriksaan',
            'fotoKoleksi',
            'allDates'
        ));
    }
}
