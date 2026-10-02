@extends('admin.layout')

@section('title', 'Dashboard Utama')
@section('page_title', 'Ringkasan Monitoring Kebun')

@section('content')
<div class="space-y-6">

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Kehadiran -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ Auth::user()->isAdmin() ? 'Kehadiran Hari Ini' : 'Status Presensi Saya' }}
                </p>
                <h3 class="text-2xl font-extrabold text-slate-800 mt-1">
                    @if(Auth::user()->isAdmin())
                        {{ $pekerjaHadir }} / {{ $totalPekerja }}
                    @else
                        {{ $pekerjaHadir > 0 ? 'Sudah Hadir' : 'Belum Hadir' }}
                    @endif
                </h3>
                <span class="inline-block mt-2 text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                    @if(Auth::user()->isAdmin())
                        {{ $totalPekerja > 0 ? round(($pekerjaHadir / $totalPekerja) * 100) : 0 }}% Tingkat Kehadiran
                    @else
                        {{ $pekerjaHadir > 0 ? 'Presensi Tercatat' : 'Silakan Presensi di App' }}
                    @endif
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-user-check"></i>
            </div>
        </div>

        <!-- Card 2: Tanaman Bermasalah -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ Auth::user()->isAdmin() ? 'Tanaman Bermasalah' : 'Temuan Tanaman Sakit' }}
                </p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ $masalahTanaman }} <span class="text-sm font-normal text-slate-500">pohon</span></h3>
                <span class="inline-block mt-2 text-xs font-medium text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md">
                    Perlu Penanganan
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>

        <!-- Card 3: Serangan Hama -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ Auth::user()->isAdmin() ? 'Laporan Hama' : 'Temuan Hama Saya' }}
                </p>
                <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $totalHama }} <span class="text-sm font-normal text-slate-500">laporan</span></h3>
                <span class="inline-block mt-2 text-xs font-medium text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                    Kutu Kebul & Ulat
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-bug"></i>
            </div>
        </div>

        <!-- Card 4: Laporan Harian -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ Auth::user()->isAdmin() ? 'Laporan Harian Masuk' : 'Laporan Harian Saya' }}
                </p>
                <h3 class="text-2xl font-extrabold text-agri-700 mt-1">{{ $laporanHariIni }} <span class="text-sm font-normal text-slate-500">laporan</span></h3>
                <span class="inline-block mt-2 text-xs font-medium text-agri-700 bg-agri-50 px-2 py-0.5 rounded-md">
                    Hari Ini Terverifikasi
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-agri-100 text-agri-700 flex items-center justify-center text-xl">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
        </div>
    </div>

    <!-- Grafik Visualisasi Interaktif Kebun -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Chart 1: Tren Kehadiran Pekerja (7 Hari) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-chart-column text-emerald-600"></i>
                        {{ Auth::user()->isAdmin() ? 'Tren Kehadiran Pekerja (7 Hari Terakhir)' : 'Riwayat Kehadiran Saya (7 Hari Terakhir)' }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ Auth::user()->isAdmin() ? 'Perbandingan jumlah pekerja hadir vs tidak hadir setiap hari' : 'Log status kehadiran presensi harian Anda' }}
                    </p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="fa-solid fa-circle text-[8px] mr-1 text-emerald-500 animate-pulse"></i> Real-time DB
                </span>
            </div>
            <div class="h-64">
                <canvas id="attendanceChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Komposisi Temuan Masalah & Hama -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-amber-500"></i>
                            {{ Auth::user()->isAdmin() ? 'Kategori Masalah Kebun' : 'Kategori Temuan Masalah Saya' }}
                        </h3>
                        <p class="text-xs text-slate-500">Distribusi jenis gangguan & hama</p>
                    </div>
                </div>
                <div class="h-56 relative flex items-center justify-center">
                    <canvas id="issuesChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Total Temuan: <strong class="text-slate-700">{{ array_sum($chartJenisData) }} Kasus</strong></span>
                <a href="{{ route('admin.laporan_masalah') }}" class="text-agri-700 font-semibold hover:underline">Kelola Detail &rarr;</a>
            </div>
        </div>
    </div>

    <!-- 2 Column Layout: Kehadiran & Status Blok Kebun -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Aktivitas Absensi Hari Ini -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800">
                        {{ Auth::user()->isAdmin() ? 'Absensi Pekerja Hari Ini' : 'Status Presensi Saya Hari Ini' }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ Auth::user()->isAdmin() ? 'Log kehadiran selfie GPS seluruh pekerja kebun real-time' : 'Log presensi kehadiran selfie GPS Anda hari ini' }}
                    </p>
                </div>
                <a href="{{ route('admin.absensi') }}" class="text-xs font-semibold text-agri-700 hover:underline">Lihat Semua &rarr;</a>
            </div>

            @if($absensiHariIni->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-calendar-xmark text-3xl mb-2"></i>
                    <p class="text-sm">Belum ada data absensi pekerja untuk hari ini.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($absensiHariIni as $absen)
                        <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <img src="{{ $absen->foto_masuk ?: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=400' }}" alt="Foto Selfie" class="w-14 h-14 rounded-2xl object-cover border-2 border-agri-200 shadow-sm flex-shrink-0">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800">{{ $absen->user->name ?? 'Pekerja' }}</h4>
                                        <span class="px-2 py-0.5 text-[11px] font-bold rounded bg-emerald-100 text-emerald-800">Hadir</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <i class="fa-regular fa-clock text-slate-400 mr-1"></i> Masuk: <span class="font-semibold text-slate-700">{{ $absen->jam_masuk ?: '-' }}</span>
                                        @if($absen->jam_pulang)
                                            • Pulang: <span class="font-semibold text-slate-700">{{ $absen->jam_pulang }}</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <i class="fa-solid fa-location-dot text-agri-600 mr-1"></i> GPS: {{ $absen->lat_masuk }}, {{ $absen->long_masuk }}
                                        <span class="text-emerald-700 font-semibold ml-1">(Radius: {{ round($absen->jarak_masuk_meter, 1) }}m - Valid)</span>
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $absen->status_pekerjaan === 'selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ ucfirst($absen->status_pekerjaan ?: 'Sedang Kerja') }}
                                </span>
                                <a href="{{ route('admin.detail_pekerja', $absen->user_id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-semibold text-slate-700 transition">
                                    Detail Laporan
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right Column: Status Blok Lahan Kebun -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800">Kondisi Blok Kebun</h3>
                        <p class="text-xs text-slate-500">Status tanaman dan hama per blok</p>
                    </div>
                    <a href="{{ route('admin.monitoring_peta') }}" class="text-xs font-semibold text-agri-700 hover:underline">Buka Peta &rarr;</a>
                </div>

                <div class="space-y-3">
                    @foreach($bloks as $blok)
                        <div class="p-4 rounded-xl border {{ $blok->status_kondisi === 'masalah' ? 'border-rose-200 bg-rose-50/50' : ($blok->status_kondisi === 'perhatian' ? 'border-amber-200 bg-amber-50/50' : 'border-emerald-200 bg-emerald-50/50') }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-3 h-3 rounded-full {{ $blok->status_kondisi === 'masalah' ? 'bg-rose-500' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                    <h4 class="font-bold text-sm text-slate-800">{{ $blok->kode_blok }}</h4>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded font-semibold uppercase {{ $blok->status_kondisi === 'masalah' ? 'bg-rose-100 text-rose-800' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $blok->status_kondisi }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-2">{{ $blok->nama_blok }}</p>
                            <div class="flex items-center gap-4 mt-3 text-xs text-slate-500">
                                <div><i class="fa-solid fa-seedling text-agri-600 mr-1"></i> {{ $blok->jumlah_tanaman }} pohon</div>
                                @if($blok->jumlah_hama > 0)
                                    <div class="text-amber-700 font-semibold"><i class="fa-solid fa-bug mr-1"></i> {{ $blok->jumlah_hama }} hama</div>
                                @endif
                                @if($blok->jumlah_masalah > 0)
                                    <div class="text-rose-600 font-semibold"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $blok->jumlah_masalah }} masalah</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Radius Geofence: <strong>50 meter</strong></span>
                <span class="text-emerald-700 font-bold"><i class="fa-solid fa-shield-check"></i> GPS Terverifikasi</span>
            </div>
        </div>

    </div>

    <!-- Laporan Masalah Tanaman Terbaru -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Laporan Masalah Tanaman & Hama Terbaru</h3>
                <p class="text-xs text-slate-500">Temuan penyakit/hama yang dilaporkan dari lapangan</p>
            </div>
            <a href="{{ route('admin.laporan_masalah') }}" class="text-xs font-semibold text-agri-700 hover:underline">Kelola Masalah &rarr;</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($masalahTerbaru as $item)
                <div class="rounded-xl border border-slate-200 p-4 hover:shadow-md transition bg-slate-50/50">
                    <div class="flex items-start gap-3">
                        @php
                            $fotos = is_array($item->foto_urls) ? $item->foto_urls : json_decode($item->foto_urls, true);
                            $firstPhoto = (!empty($fotos) && is_array($fotos)) ? $fotos[0] : 'https://images.unsplash.com/photo-1592417817098-8f3d6910985b?w=600';
                        @endphp
                        <img src="{{ $firstPhoto }}" alt="Foto Masalah" class="w-16 h-16 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-rose-100 text-rose-800">{{ $item->jenis_masalah }}</span>
                                <span class="text-[11px] text-slate-400">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-800 mt-1 truncate">{{ $item->baris }}</h4>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $item->jumlah_tanaman }} tanaman • {{ $item->kondisi }}</p>
                            <p class="text-xs text-slate-500 mt-1 italic line-clamp-1">"{{ $item->catatan }}"</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chart Kehadiran (Bar Chart)
    const ctxAttendance = document.getElementById('attendanceChart');
    if (ctxAttendance) {
        new Chart(ctxAttendance, {
            type: 'bar',
            data: {
                labels: @json($chartDates),
                datasets: [
                    {
                        label: 'Pekerja Hadir',
                        data: @json($chartHadir),
                        backgroundColor: '#10b981',
                        borderRadius: 6,
                    },
                    {
                        label: 'Tidak Hadir',
                        data: @json($chartTidakHadir),
                        backgroundColor: '#e2e8f0',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, font: { family: 'Plus Jakarta Sans', size: 11 } },
                        grid: { color: '#f1f5f9' }
                    }
                }
            }
        });
    }

    // 2. Chart Kategori Masalah (Doughnut Chart)
    const ctxIssues = document.getElementById('issuesChart');
    if (ctxIssues) {
        new Chart(ctxIssues, {
            type: 'doughnut',
            data: {
                labels: @json($chartJenisLabels),
                datasets: [{
                    data: @json($chartJenisData),
                    backgroundColor: ['#f59e0b', '#ef4444', '#10b981', '#6366f1', '#ec4899', '#8b5cf6'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            font: { family: 'Plus Jakarta Sans', size: 11 }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    }
});
</script>
@endpush
