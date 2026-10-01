@extends('admin.layout')

@section('title', 'Data Pekerja Kebun')
@section('page_title', 'Manajemen Pekerja Kebun')

@section('content')
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-slate-800">Daftar Pekerja & Mandor Lapangan</h3>
            <p class="text-xs text-slate-500">Akun pekerja untuk eksekusi aplikasi mobile AGROCOM</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-lg bg-agri-100 text-agri-800 font-bold text-xs">
                Total: {{ count($pekerjaList) }} Pekerja
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="p-4">Nama Pekerja</th>
                    <th class="p-4">Username Login</th>
                    <th class="p-4">No. HP / WhatsApp</th>
                    <th class="p-4">Peran (Role)</th>
                    <th class="p-4">Status Akun</th>
                    <th class="p-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($pekerjaList as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-agri-700 text-white flex items-center justify-center font-bold text-sm shadow">
                                {{ strtoupper(substr($p->name, 0, 1)) }}
                            </div>
                            <div>
                                <div class="font-bold text-slate-800">{{ $p->name }}</div>
                                <div class="text-xs text-slate-400">{{ $p->email }}</div>
                            </div>
                        </td>
                        <td class="p-4 font-mono font-semibold text-agri-800">
                            {{ $p->username }}
                        </td>
                        <td class="p-4">
                            {{ $p->phone ?: '-' }}
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $p->role }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('admin.detail_pekerja', $p->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                                Lihat Laporan
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
