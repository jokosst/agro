@extends('admin.layout')

@section('title', 'Detail Laporan Pekerja - ' . $pekerja->name)
@section('page_title', 'Detail Laporan Kerja Harian')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Profil & Kehadiran (Sesuai Storyboard Mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
            
            <!-- Left Info (Avatar Foto Asli + Info Pekerja) -->
            <div class="flex items-center gap-4 min-w-0">
                @if($absensi && $absensi->foto_masuk)
                    <img src="{{ $absensi->foto_masuk }}" alt="{{ $pekerja->name }}" 
                        onclick="showPhotoModal('{{ $absensi->foto_masuk }}', 'Foto Absen Masuk - {{ $pekerja->name }}')"
                        class="w-16 h-16 min-w-[64px] min-h-[64px] max-w-[64px] max-h-[64px] rounded-2xl object-cover border-2 border-emerald-500 shadow-sm cursor-pointer hover:scale-105 transition flex-shrink-0">
                @else
                    <div class="w-16 h-16 min-w-[64px] min-h-[64px] max-w-[64px] max-h-[64px] rounded-2xl bg-emerald-700 text-white flex items-center justify-center font-extrabold text-xl shadow-sm border-2 border-emerald-200 flex-shrink-0">
                        {{ strtoupper(substr($pekerja->name, 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xl font-extrabold text-slate-800 truncate">{{ $pekerja->name }}</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $absensi && $absensi->jam_masuk ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                            {{ $absensi && $absensi->jam_masuk ? 'Hadir' : 'Tidak Hadir' }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 mt-1 truncate">
                        <i class="fa-solid fa-location-dot text-emerald-600 mr-1"></i> {{ $pekerja->kebun->nama ?? 'Kebun Cabai Agrocom - Sambas' }}
                    </p>

                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-600">
                        <span class="font-medium text-slate-700">
                            {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                        </span>
                        <span class="text-slate-300">•</span>
                        <span>Jam Kerja:</span>
                        @if($absensi && $absensi->jam_masuk)
                            <strong class="font-mono font-bold text-slate-800">
                                {{ $absensi->jam_masuk }} &ndash; {{ $absensi->jam_pulang ?: 'Masih Bekerja (Belum Pulang)' }}
                            </strong>
                        @else
                            <span class="text-slate-400 italic">Belum melakukan absen</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Controls: Date Selector & Problem Badges -->
            <div class="flex flex-col sm:items-end gap-2.5 flex-shrink-0">
                <!-- Date Selector Filter -->
                <form method="GET" action="{{ route('admin.detail_pekerja', $pekerja->id) }}" class="flex items-center gap-2">
                    <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pilih Tanggal:</label>
                    <select name="date" onchange="this.form.submit()" 
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50 focus:border-agri-600 focus:bg-white transition cursor-pointer">
                        @foreach($allDates as $dt)
                            <option value="{{ $dt }}" {{ $selectedDate === $dt ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($dt)->translatedFormat('d M Y') }}
                            </option>
                        @endforeach
                        @if(!in_array(\Carbon\Carbon::today()->toDateString(), $allDates->toArray()))
                            <option value="{{ \Carbon\Carbon::today()->toDateString() }}" {{ $selectedDate === \Carbon\Carbon::today()->toDateString() ? 'selected' : '' }}>
                                Hari Ini ({{ \Carbon\Carbon::today()->translatedFormat('d M Y') }})
                            </option>
                        @endif
                    </select>
                </form>

                <!-- Dynamic Problem Badges Sesuai Mockup -->
                <div class="flex flex-wrap items-center gap-2">
                    @if($masalahCount > 0)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-triangle-exclamation text-rose-500"></i> {{ $masalahCount }} Masalah Tanaman
                        </span>
                    @endif
                    @if($hamaCount > 0)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-bug text-amber-500"></i> {{ $hamaCount }} Laporan Hama
                        </span>
                    @endif
                    @if($masalahCount === 0 && $hamaCount === 0)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5 shadow-sm">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Kebun Kondisi Bersih & Sehat
                        </span>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- 1. Daftar Pekerjaan Hari Ini (Checklist Grid Sesuai Mockup) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        @php
            $completedCount = $tugasList->where('is_completed', true)->count();
            $totalTasks = count($tugasList);
            $percentage = $totalTasks > 0 ? round(($completedCount / $totalTasks) * 100) : 0;
        @endphp

        <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold text-base text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-emerald-600"></i>
                Daftar Pekerjaan Hari Ini
            </h4>
            <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-500">{{ $completedCount }} dari {{ $totalTasks }} Selesai</span>
                <span class="px-2 py-0.5 rounded-md text-xs font-extrabold {{ $percentage === 100 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ $percentage }}%
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse($tugasList as $task)
                <div class="p-3.5 rounded-xl border flex items-center gap-3 transition {{ $task->is_completed ? 'border-emerald-300 bg-emerald-50/60 text-slate-800' : 'border-slate-200 bg-slate-50/50 text-slate-400' }}">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 {{ $task->is_completed ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-200 text-transparent' }}">
                        <i class="fa-solid fa-check text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold truncate {{ $task->is_completed ? 'text-slate-800' : 'text-slate-400' }}">
                        {{ $task->judul }}
                    </span>
                </div>
            @empty
                <div class="col-span-2 text-center py-6 text-slate-400 text-xs">
                    Belum ada master tugas harian yang dikonfigurasi.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2. Temuan Kondisi Tanaman (3 Kotak Utama Sesuai Mockup Storyboard) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-5">
        <h4 class="font-bold text-base text-slate-800 flex items-center gap-2">
            <i class="fa-solid fa-magnifying-glass text-emerald-600"></i>
            Temuan Kondisi Tanaman
        </h4>

        @php
            // Ambil rincian keterangan hama dari DB
            $hamaList = $masalahList->where('jenis_masalah', 'Hama');
            $hamaSummaryNote = $hamaList->pluck('catatan')->filter()->implode(', ') ?: ($hamaCount > 0 ? 'Ditemukan kutu kebul / daun keriting' : 'Kondisi tanaman bebas hama');

            // Ambil rincian layu / penyakit dari DB
            $layuList = $masalahList->where('jenis_masalah', '!=', 'Hama');
            $layuLocation = $layuList->map(fn($m) => ($m->blok->kode_blok ?? 'Blok') . ' - ' . ($m->baris ?: 'Lahan'))->filter()->implode(', ') ?: ($masalahCount > 0 ? 'Perlu tindakan lanjutan' : 'Semua blok subur');
        @endphp

        <!-- 3 Kartu Berwarna: Serangan Hama, Tanaman Layu, Kondisi Gulma -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            
            <!-- Kotak 1: Serangan Hama (Kuning/Amber) -->
            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-amber-700 block">
                        Serangan Hama
                    </span>
                    <div class="text-2xl font-black text-amber-900 mt-2">
                        {{ $hamaCount > 0 ? $hamaCount . ' Tanaman' : 'Bebas Hama' }}
                    </div>
                </div>
                <p class="text-xs text-amber-800 mt-3 font-medium line-clamp-2">
                    {{ $hamaSummaryNote }}
                </p>
            </div>

            <!-- Kotak 2: Tanaman Layu (Merah Muda/Rose) -->
            <div class="rounded-2xl border border-rose-200 bg-rose-50/60 p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-rose-700 block">
                        Tanaman Layu / Masalah
                    </span>
                    <div class="text-2xl font-black text-rose-900 mt-2">
                        {{ $masalahCount > 0 ? $masalahCount . ' Tanaman' : 'Kondisi Prima' }}
                    </div>
                </div>
                <p class="text-xs text-rose-800 mt-3 font-medium line-clamp-2">
                    {{ $layuLocation }}
                </p>
            </div>

            <!-- Kotak 3: Kondisi Gulma (Hijau/Emerald) -->
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5 flex flex-col justify-between">
                <div>
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 block">
                        Kondisi Gulma
                    </span>
                    <div class="text-2xl font-black text-emerald-900 mt-2">
                        {{ $laporanHarian->gulma ?? 'Bersih' }}
                    </div>
                </div>
                <p class="text-xs text-emerald-800 mt-3 font-medium line-clamp-2">
                    {{ $laporanHarian->kendala ? 'Catatan: ' . $laporanHarian->kendala : 'Sebagian telah dibersihkan' }}
                </p>
            </div>

        </div>

        <!-- Detail Tambahan Parameter Lapangan (Tiang, Perempelan, Pemupukan, Penyemprotan) -->
        @if($laporanHarian)
            <div class="pt-4 border-t border-slate-100">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">🪢 Tiang & Ajir</span>
                        <strong class="text-sm font-extrabold text-slate-800 mt-0.5 block">{{ $laporanHarian->ajir }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">✂️ Perempelan</span>
                        <strong class="text-sm font-extrabold text-slate-800 mt-0.5 block">{{ $laporanHarian->perempelan }}</strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">🧪 Pemupukan</span>
                        <strong class="text-sm font-extrabold {{ $laporanHarian->pemupukan === 'Dilakukan' ? 'text-emerald-700' : 'text-slate-600' }} mt-0.5 block">
                            {{ $laporanHarian->pemupukan }}
                        </strong>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 font-bold uppercase text-[10px] block">💨 Penyemprotan</span>
                        <strong class="text-sm font-extrabold {{ $laporanHarian->penyemprotan === 'Dilakukan' ? 'text-emerald-700' : 'text-slate-600' }} mt-0.5 block">
                            {{ $laporanHarian->penyemprotan }}
                        </strong>
                    </div>
                </div>
            </div>
        @endif

        <!-- Rincian Bagian Fisik Tanaman -->
        @if($pemeriksaan)
            <div class="pt-2">
                <span class="text-xs font-bold text-slate-700 block mb-2">Pemeriksaan Organ Fisik Bagian Tanaman:</span>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-xs">
                    <div class="p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-400 text-[10px] uppercase block">Daun:</span>
                        <strong class="text-slate-800">{{ $pemeriksaan->kondisi_daun }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-400 text-[10px] uppercase block">Batang:</span>
                        <strong class="text-slate-800">{{ $pemeriksaan->kondisi_batang }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-400 text-[10px] uppercase block">Bunga:</span>
                        <strong class="text-slate-800">{{ $pemeriksaan->kondisi_bunga }}</strong>
                    </div>
                    <div class="p-2.5 rounded-lg border border-slate-200">
                        <span class="text-slate-400 text-[10px] uppercase block">Buah:</span>
                        <strong class="text-slate-800">{{ $pemeriksaan->kondisi_buah }}</strong>
                    </div>
                </div>
                @if($pemeriksaan->catatan)
                    <p class="text-xs text-slate-500 italic mt-2">"{{ $pemeriksaan->catatan }}"</p>
                @endif
            </div>
        @endif
    </div>

    <!-- 3. Dokumentasi Lapangan (Tersimpan di Google Drive) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-base text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-camera text-emerald-600"></i>
                Dokumentasi Lapangan (Tersimpan di Google Drive)
            </h4>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-700">
                {{ count($fotoKoleksi) }} Foto Dokumentasi
            </span>
        </div>

        @if(!empty($fotoKoleksi))
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($fotoKoleksi as $foto)
                    <div class="rounded-2xl overflow-hidden border border-slate-200 group relative aspect-[4/3] bg-slate-100 cursor-pointer shadow-sm hover:shadow-md transition"
                        onclick="showPhotoModal('{{ $foto['url'] }}', '{{ $foto['title'] }}')">
                        <img src="{{ $foto['url'] }}" alt="{{ $foto['title'] }}" 
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-90 p-3 flex flex-col justify-between">
                            <span class="self-start px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-black/60 backdrop-blur-md text-white border border-white/20">
                                {{ $foto['badge'] }}
                            </span>
                            <div>
                                <h6 class="text-white text-xs font-bold truncate">{{ $foto['title'] }}</h6>
                                <span class="text-[10px] text-emerald-300 font-semibold">{{ $foto['time'] }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-slate-100 text-slate-400 text-xs">
                <i class="fa-solid fa-images text-2xl mb-2 text-slate-300 block"></i>
                Tidak ada foto dokumentasi yang diunggah untuk tanggal {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}.
            </div>
        @endif
    </div>

</div>

<!-- Photo Modal Viewer (Klik untuk perbesar) -->
<div id="photoModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4" onclick="closePhotoModal()">
    <div class="max-w-3xl w-full bg-slate-900 rounded-3xl overflow-hidden shadow-2xl border border-white/20" onclick="event.stopPropagation()">
        <div class="p-4 border-b border-white/10 flex items-center justify-between text-white">
            <h5 id="modalPhotoTitle" class="font-bold text-sm truncate"></h5>
            <button onclick="closePhotoModal()" class="text-white/70 hover:text-white p-1 text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-2 bg-black flex items-center justify-center max-h-[75vh]">
            <img id="modalPhotoImage" src="" alt="Preview" class="max-h-[70vh] w-auto object-contain rounded-xl">
        </div>
        <div class="p-3 bg-slate-900 text-center">
            <a id="modalPhotoLink" href="" target="_blank" class="text-xs font-bold text-emerald-400 hover:underline">
                <i class="fa-solid fa-arrow-up-right-from-square mr-1"></i> Buka Ukuran Asli di Google Drive
            </a>
        </div>
    </div>
</div>

<script>
    function showPhotoModal(url, title) {
        document.getElementById('modalPhotoImage').src = url;
        document.getElementById('modalPhotoTitle').innerText = title;
        document.getElementById('modalPhotoLink').href = url;
        document.getElementById('photoModal').classList.remove('hidden');
    }
    function closePhotoModal() {
        document.getElementById('photoModal').classList.add('hidden');
    }
</script>
@endsection
