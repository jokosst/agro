@extends('admin.layout')

@section('title', 'Rekap Absensi Pekerja')
@section('page_title', 'Rekapitulasi Kehadiran Pekerja')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Header Filter -->
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-slate-800">Daftar Absen Masuk & Pulang</h3>
            <p class="text-xs text-slate-500">Dilengkapi foto selfie verifikasi dan titik koordinat GPS kebun</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200">
                <i class="fa-solid fa-satellite-dish mr-1"></i> Radius Max: 50 Meter
            </span>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4">Tanggal & Pekerja</th>
                    <th class="p-4">Foto Selfie Masuk</th>
                    <th class="p-4">Jam & GPS Masuk</th>
                    <th class="p-4">Foto Selfie Pulang</th>
                    <th class="p-4">Jam & GPS Pulang</th>
                    <th class="p-4">Status Pekerjaan</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($absensiList as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->user->name ?? 'Pekerja' }}</div>
                            <div class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}</div>
                            <span class="inline-block mt-1 text-[11px] px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium">
                                {{ $item->user->role ?? 'pekerja' }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($item->foto_masuk)
                                <a href="{{ $item->foto_masuk }}" target="_blank" class="block group relative w-12 h-12 rounded-xl overflow-hidden shadow-sm border border-slate-200">
                                    <img src="{{ $item->foto_masuk }}" alt="Selfie Masuk" class="w-full h-full object-cover group-hover:scale-110 transition">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </div>
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->jam_masuk ?? '-' }}</div>
                            @if($item->lat_masuk)
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->lat_masuk }}, {{ $item->long_masuk }}</div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold {{ $item->is_valid_geofence_masuk ? 'text-emerald-700' : 'text-rose-600' }}">
                                    <i class="fa-solid {{ $item->is_valid_geofence_masuk ? 'fa-check' : 'fa-xmark' }}"></i>
                                    {{ round($item->jarak_masuk_meter, 1) }}m ({{ $item->is_valid_geofence_masuk ? 'Valid' : 'Luar Radius' }})
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($item->foto_pulang)
                                <a href="{{ $item->foto_pulang }}" target="_blank" class="block group relative w-12 h-12 rounded-xl overflow-hidden shadow-sm border border-slate-200">
                                    <img src="{{ $item->foto_pulang }}" alt="Selfie Pulang" class="w-full h-full object-cover group-hover:scale-110 transition">
                                    <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white text-xs transition">
                                        <i class="fa-solid fa-eye"></i>
                                    </div>
                                </a>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum absen</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $item->jam_pulang ?? '-' }}</div>
                            @if($item->lat_pulang)
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $item->lat_pulang }}, {{ $item->long_pulang }}</div>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold {{ $item->is_valid_geofence_pulang ? 'text-emerald-700' : 'text-rose-600' }}">
                                    <i class="fa-solid {{ $item->is_valid_geofence_pulang ? 'fa-check' : 'fa-xmark' }}"></i>
                                    {{ round($item->jarak_pulang_meter, 1) }}m
                                </span>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $item->status_pekerjaan === 'selesai' ? 'bg-emerald-100 text-emerald-800' : ($item->status_pekerjaan === 'sebagian' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                {{ $item->status_pekerjaan ?: 'Belum selesai' }}
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('admin.detail_pekerja', $item->user_id) }}" class="px-3 py-1.5 rounded-lg bg-agri-600 hover:bg-agri-700 text-white font-semibold text-xs transition shadow-sm">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-slate-400">
                            Belum ada riwayat absensi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($absensiList->hasPages())
        <div class="p-4 border-t border-slate-200">
            {{ $absensiList->links() }}
        </div>
    @endif
</div>
@endsection
