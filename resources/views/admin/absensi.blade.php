@extends('admin.layout')

@section('title', 'Rekap Absensi Pekerja')
@section('page_title', 'Rekapitulasi Kehadiran Pekerja')

@section('content')
<div class="space-y-4">

    <!-- Filters & Search Bar Card -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.absensi') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            @if(Auth::user()->isAdmin())
            <!-- Search Text (Admin Only) -->
            <div class="relative flex-1 min-w-[200px] sm:min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Cari nama, username..." 
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-800 font-medium focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10 placeholder:text-slate-400">
            </div>
            @endif

            <!-- Date Filter -->
            <div class="flex items-center gap-1.5">
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()"
                    title="Filter Tanggal"
                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium focus:border-agri-600 cursor-pointer">
            </div>

            @if(Auth::user()->isAdmin())
            <!-- Worker Dropdown (Admin Only) -->
            <select name="pekerja_id" onchange="this.form.submit()" 
                class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium focus:border-agri-600 cursor-pointer">
                <option value="">Semua Pekerja</option>
                @foreach($pekerjaList as $p)
                    <option value="{{ $p->id }}" {{ request('pekerja_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
            @else
            <div class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-xs text-slate-600 font-bold flex items-center gap-1.5">
                <i class="fa-solid fa-user text-agri-600"></i>
                <span>Data Saya: {{ Auth::user()->name }}</span>
            </div>
            @endif

            <!-- Status Pekerjaan Dropdown -->
            <select name="status" onchange="this.form.submit()" 
                class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium focus:border-agri-600 cursor-pointer">
                <option value="">Semua Status</option>
                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="sebagian" {{ request('status') === 'sebagian' ? 'selected' : '' }}>Sebagian</option>
                <option value="belum_selesai" {{ request('status') === 'belum_selesai' ? 'selected' : '' }}>Belum Selesai</option>
            </select>

            <!-- Per Page Selector -->
            <select name="per_page" onchange="this.form.submit()" 
                title="Jumlah data per halaman"
                class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium focus:border-agri-600 cursor-pointer">
                <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5 / hal</option>
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / hal</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / hal</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / hal</option>
            </select>

            <!-- Submit Button -->
            <button type="submit" 
                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white transition shadow-sm flex items-center gap-1.5">
                <i class="fa-solid fa-filter text-[10px]"></i>
                <span>Filter</span>
            </button>

            <!-- Reset Button -->
            @if(request()->hasAny(['search', 'tanggal', 'pekerja_id', 'status', 'per_page']))
                <a href="{{ route('admin.absensi') }}" 
                    class="text-xs text-rose-600 hover:text-rose-700 hover:underline px-2 py-1 font-semibold flex items-center gap-1">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i> Reset
                </a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium whitespace-nowrap self-end lg:self-center">
            Total: <span class="font-extrabold text-slate-800">{{ $absensiList->total() }}</span> Data Absensi
        </div>
    </div>

    <!-- Table Container Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Header Subtitle & Radius Info -->
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Daftar Absen Masuk & Pulang</h3>
                <p class="text-xs text-slate-500 mt-0.5">Dilengkapi foto selfie verifikasi dan titik koordinat GPS kebun</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-xs border border-emerald-200 flex items-center gap-1.5">
                    <i class="fa-solid fa-satellite-dish text-emerald-600"></i>
                    <span>Radius Max: 50 Meter</span>
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
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.detail_pekerja', $item->user_id) }}" 
                                        class="p-2 rounded-lg text-slate-500 hover:text-agri-700 hover:bg-emerald-50 transition" title="Lihat Rekap Kerja">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    @if(Auth::user()->isAdmin())
                                    <button type="button" onclick='openEditAbsensiModal(@json($item))'
                                        class="p-2 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition" title="Edit Data Absensi">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.absensi.destroy', $item->id) }}" method="POST" 
                                        onsubmit="return confirm('Hapus riwayat absensi tanggal {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }} untuk {{ $item->user->name ?? 'pekerja' }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Riwayat">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak ada data absensi ditemukan</p>
                                    <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter</p>
                                    @if(request()->hasAny(['search', 'tanggal', 'pekerja_id', 'status']))
                                        <a href="{{ route('admin.absensi') }}" class="mt-3 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                                            Reset Filter
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination & Summary Footer -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/60 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            <div>
                Menampilkan <strong class="font-extrabold text-slate-800">{{ $absensiList->firstItem() ?? 0 }}</strong> - <strong class="font-extrabold text-slate-800">{{ $absensiList->lastItem() ?? 0 }}</strong> dari <strong class="font-extrabold text-slate-800">{{ $absensiList->total() }}</strong> riwayat absensi
            </div>
            <div>
                @if($absensiList->hasPages())
                    {{ $absensiList->links() }}
                @else
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium select-none">
                        <span class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-300 cursor-not-allowed flex items-center gap-1">
                            <i class="fa-solid fa-chevron-left text-[10px]"></i> Sebelumnya
                        </span>
                        <span class="px-3 py-1.5 rounded-lg bg-agri-600 text-white font-bold shadow-sm">1</span>
                        <span class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-300 cursor-not-allowed flex items-center gap-1">
                            Selanjutnya <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>

@if(Auth::user()->isAdmin())
<!-- MODAL EDIT ABSENSI -->
<div id="modalEditAbsensi" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-base text-slate-800">Edit Data Absensi Pekerja</h4>
                <p id="editPekerjaName" class="text-xs text-slate-500 font-medium mt-0.5"></p>
            </div>
            <button onclick="closeEditAbsensiModal()" class="text-slate-400 hover:text-slate-600 p-1">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <form id="formEditAbsensiAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Absensi *</label>
                <input type="date" id="editTanggal" name="tanggal" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Masuk</label>
                    <input type="time" step="1" id="editJamMasuk" name="jam_masuk"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:border-agri-600">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Pulang</label>
                    <input type="time" step="1" id="editJamPulang" name="jam_pulang"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:border-agri-600">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Pekerjaan</label>
                    <select id="editStatusPekerjaan" name="status_pekerjaan"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:border-agri-600">
                        <option value="selesai">Selesai</option>
                        <option value="sebagian">Sebagian</option>
                        <option value="belum">Belum Selesai</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Geofence Masuk</label>
                    <select id="editGeofenceMasuk" name="is_valid_geofence_masuk"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold focus:border-agri-600">
                        <option value="1">Valid (Radius &le; 50m)</option>
                        <option value="0">Luar Radius</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea id="editCatatan" name="catatan" rows="2" placeholder="Catatan opsional dari admin..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditAbsensiModal()" 
                    class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" 
                    class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditAbsensiModal(item) {
        document.getElementById('formEditAbsensiAction').action = `/admin/absensi/${item.id}`;
        document.getElementById('editPekerjaName').innerText = `Pekerja: ${item.user ? item.user.name : 'Pekerja'}`;
        
        const tgl = item.tanggal ? item.tanggal.split('T')[0] : '';
        document.getElementById('editTanggal').value = tgl;
        document.getElementById('editJamMasuk').value = item.jam_masuk || '';
        document.getElementById('editJamPulang').value = item.jam_pulang || '';
        
        let status = item.status_pekerjaan || 'belum';
        if (status === 'belum_selesai') status = 'belum';
        document.getElementById('editStatusPekerjaan').value = status;
        document.getElementById('editGeofenceMasuk').value = item.is_valid_geofence_masuk ? '1' : '0';
        document.getElementById('editCatatan').value = item.catatan || '';
        
        document.getElementById('modalEditAbsensi').classList.remove('hidden');
    }

    function closeEditAbsensiModal() {
        document.getElementById('modalEditAbsensi').classList.add('hidden');
    }
</script>
@endif
@endsection
