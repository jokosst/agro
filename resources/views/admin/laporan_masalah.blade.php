@extends('admin.layout')

@section('title', 'Laporan Masalah & Hama')
@section('page_title', 'Laporan Masalah Tanaman & Hama')

@section('content')
<div class="space-y-6">

    <!-- KPI Summary Cards (Real Database Calculations) -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Total Temuan</span>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ $totalTemuan }}</p>
            <span class="text-[11px] text-slate-500 font-medium">Laporan masuk</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-rose-200 bg-rose-50/20 shadow-sm">
            <span class="text-[11px] text-rose-600 font-bold uppercase tracking-wider block">Menunggu</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $menungguCount }}</p>
            <span class="text-[11px] text-rose-600 font-medium">Perlu penanganan</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-blue-200 bg-blue-50/20 shadow-sm">
            <span class="text-[11px] text-blue-600 font-bold uppercase tracking-wider block">Sedang Ditangani</span>
            <p class="text-2xl font-black text-blue-600 mt-1">{{ $ditanganiCount }}</p>
            <span class="text-[11px] text-blue-600 font-medium">Dalam proses</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-emerald-200 bg-emerald-50/20 shadow-sm">
            <span class="text-[11px] text-emerald-600 font-bold uppercase tracking-wider block">Selesai</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $selesaiCount }}</p>
            <span class="text-[11px] text-emerald-600 font-medium">Terselesaikan</span>
        </div>
        <div class="bg-white p-4 rounded-2xl border border-amber-200 bg-amber-50/20 shadow-sm col-span-2 md:col-span-1">
            <span class="text-[11px] text-amber-600 font-bold uppercase tracking-wider block">Pohon Sakit</span>
            <p class="text-2xl font-black text-amber-700 mt-1">{{ number_format($totalPohonSakit) }}</p>
            <span class="text-[11px] text-amber-600 font-medium">Tanaman terdampak</span>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.laporan_masalah') }}" class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto flex-1">
                <!-- Search Box -->
                <div class="relative flex-1 sm:w-60">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelapor, kondisi, catatan..." 
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>

                <!-- Filter Jenis Masalah -->
                <select name="jenis" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <option value="semua">Semua Jenis Masalah</option>
                    <option value="Hama" {{ request('jenis') === 'Hama' ? 'selected' : '' }}>Hama</option>
                    <option value="Penyakit" {{ request('jenis') === 'Penyakit' ? 'selected' : '' }}>Penyakit</option>
                    <option value="Gulma" {{ request('jenis') === 'Gulma' ? 'selected' : '' }}>Gulma</option>
                </select>

                <!-- Filter Status -->
                <select name="status" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <option value="semua">Semua Status</option>
                    <option value="menunggu" {{ request('status') === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                    <option value="ditangani" {{ request('status') === 'ditangani' ? 'selected' : '' }}>Sedang Ditangani</option>
                    <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>

                <!-- Filter Blok -->
                <select name="blok_id" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700">
                    <option value="semua">Semua Blok Lahan</option>
                    @foreach($bloks as $b)
                        <option value="{{ $b->id }}" {{ request('blok_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->kode_blok }} - {{ $b->nama_blok }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                    Terapkan
                </button>

                @if(request()->hasAny(['search', 'jenis', 'status', 'blok_id']))
                    <a href="{{ route('admin.laporan_masalah') }}" class="text-xs text-rose-600 hover:underline px-2 py-1 font-bold">Reset</a>
                @endif
            </div>

            <div class="text-xs text-slate-500 font-semibold">
                Menampilkan <strong class="text-slate-800">{{ $masalahList->total() }}</strong> Laporan
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Laporan Masalah -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Tanggal & Pelapor</th>
                        <th class="p-4">Lokasi / Blok</th>
                        <th class="p-4 text-center">Jenis</th>
                        <th class="p-4 text-center">Pohon Terdampak</th>
                        <th class="p-4">Temuan & Gejala</th>
                        <th class="p-4">Dokumentasi Foto</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Ubah Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($masalahList as $m)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-800">{{ $m->user->name ?? 'Pekerja' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $m->created_at ? $m->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">
                                    {{ $m->blok->kode_blok ?? 'Blok Kebun' }}
                                </div>
                                <span class="text-xs text-slate-500">{{ $m->baris ?: 'Semua Baris' }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($m->jenis_masalah === 'Hama')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-bug text-[10px] mr-1"></i> Hama
                                    </span>
                                @elseif($m->jenis_masalah === 'Penyakit')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <i class="fa-solid fa-virus text-[10px] mr-1"></i> Penyakit
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-seedling text-[10px] mr-1"></i> {{ $m->jenis_masalah }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-extrabold text-slate-800">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-800">
                                    {{ $m->jumlah_tanaman }} Tanaman
                                </span>
                            </td>
                            <td class="p-4 max-w-xs">
                                <div class="font-bold text-slate-800 text-xs">{{ $m->kondisi ?: '-' }}</div>
                                @if($m->catatan)
                                    <p class="text-xs text-slate-500 italic mt-0.5 line-clamp-2">"{{ $m->catatan }}"</p>
                                @endif
                            </td>
                            <td class="p-4">
                                @php
                                    $fotos = is_array($m->foto_urls) ? $m->foto_urls : json_decode($m->foto_urls, true);
                                @endphp
                                <div class="flex items-center gap-1.5">
                                    @if(!empty($fotos) && is_array($fotos))
                                        @foreach(array_slice($fotos, 0, 3) as $pic)
                                            <div onclick="showPhotoModal('{{ $pic }}', 'Dokumentasi {{ $m->jenis_masalah }} - {{ $m->blok->kode_blok ?? 'Kebun' }}')"
                                                class="w-10 h-10 rounded-xl overflow-hidden border border-slate-200 cursor-pointer hover:scale-105 transition shadow-sm bg-slate-100 flex-shrink-0">
                                                <img src="{{ $pic }}" alt="Foto Masalah" class="w-full h-full object-cover">
                                            </div>
                                        @endforeach
                                        @if(count($fotos) > 3)
                                            <span class="text-[10px] text-slate-400 font-bold ml-1">+{{ count($fotos) - 3 }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-center">
                                @if($m->status === 'selesai')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center justify-center gap-1 mx-auto w-fit">
                                        <i class="fa-solid fa-circle-check text-[10px]"></i> Selesai
                                    </span>
                                @elseif($m->status === 'ditangani')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 flex items-center justify-center gap-1 mx-auto w-fit">
                                        <i class="fa-solid fa-spinner animate-spin text-[10px]"></i> Ditangani
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200 flex items-center justify-center gap-1 mx-auto w-fit">
                                        <i class="fa-solid fa-clock text-[10px]"></i> Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.laporan_masalah.status', $m->id) }}" method="POST" class="inline-flex items-center gap-1">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" 
                                        class="px-2.5 py-1 rounded-lg text-xs font-bold border border-slate-200 bg-slate-50 focus:border-agri-600 cursor-pointer">
                                        <option value="menunggu" {{ $m->status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="ditangani" {{ $m->status === 'ditangani' ? 'selected' : '' }}>Ditangani</option>
                                        <option value="selesai" {{ $m->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-12 text-slate-400">
                                <i class="fa-solid fa-shield-virus text-3xl mb-2 text-slate-300 block"></i>
                                Tidak ada laporan masalah tanaman atau serangan hama yang sesuai dengan filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($masalahList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $masalahList->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Photo Modal Viewer -->
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
