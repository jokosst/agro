@extends('admin.layout')

@section('title', 'Data Kebun & Blok Lahan')
@section('page_title', 'Master Data Kebun & Blok Lahan')

@section('content')
<div class="space-y-6">

    <!-- Card Info Kebun -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-4">
            <div>
                <h3 class="font-bold text-xl text-slate-800">{{ $kebun->nama ?? 'Kebun Cabai Agrocom' }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    <i class="fa-solid fa-location-dot text-agri-600 mr-1"></i> {{ $kebun->lokasi_text ?? 'Sambas, Kalimantan Barat' }}
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800">
                Status: {{ $kebun->status ?? 'Aktif' }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 text-sm">
            <div class="p-3 bg-slate-50 rounded-xl">
                <span class="text-xs text-slate-400 font-semibold uppercase block">Koordinat Pusat GPS</span>
                <span class="font-mono font-bold text-slate-800">{{ $kebun->latitude }}, {{ $kebun->longitude }}</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <span class="text-xs text-slate-400 font-semibold uppercase block">Radius Toleransi Geofence</span>
                <span class="font-bold text-emerald-700">{{ $kebun->radius_meter ?? 50 }} Meter (Valid $\le 50$m)</span>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl">
                <span class="text-xs text-slate-400 font-semibold uppercase block">Luas Area Kebun</span>
                <span class="font-bold text-slate-800">{{ $kebun->luas_lahan ?? '2 Hektar' }}</span>
            </div>
        </div>
    </div>

    <!-- Daftar Blok Lahan -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h4 class="font-bold text-lg text-slate-800">Pembagian Blok Lahan Cabai</h4>
            <p class="text-xs text-slate-500">Monitoring status tanaman dan serangan hama per blok</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Kode Blok</th>
                        <th class="p-4">Deskripsi Blok</th>
                        <th class="p-4">Jumlah Pohon</th>
                        <th class="p-4">Serangan Hama</th>
                        <th class="p-4">Status Kondisi</th>
                        <th class="p-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($kebun->bloks as $blok)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 font-bold text-slate-800">
                                {{ $blok->kode_blok }}
                            </td>
                            <td class="p-4 font-medium text-slate-700">
                                {{ $blok->nama_blok }}
                            </td>
                            <td class="p-4 font-bold text-slate-800">
                                {{ $blok->jumlah_tanaman }} pohon
                            </td>
                            <td class="p-4">
                                <span class="font-bold {{ $blok->jumlah_hama > 0 ? 'text-amber-600' : 'text-slate-500' }}">
                                    {{ $blok->jumlah_hama }} tanaman
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase {{ $blok->status_kondisi === 'masalah' ? 'bg-rose-100 text-rose-800' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                    {{ $blok->status_kondisi }}
                                </span>
                            </td>
                            <td class="p-4 text-xs text-slate-500">
                                {{ $blok->keterangan ?: '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
