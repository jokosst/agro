@extends('admin.layout')

@section('title', 'Master Lokasi & Blok Lahan')
@section('page_title', 'Master Lokasi & Blok Lahan Kebun')

@section('content')
<div class="space-y-6">

    <!-- Card 1: Informasi Induk Kebun -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-5 border-b border-slate-100">
            <div>
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-mountain-sun text-agri-600"></i>
                    Informasi Utama Kebun Cabai
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Konfigurasi koordinat GPS sentral kebun & batas radius geofencing presisi absensi</p>
            </div>
            <div>
                <button type="button" onclick="toggleEditKebunForm()" 
                    class="px-4 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Ubah Info Kebun</span>
                </button>
            </div>
        </div>

        <!-- Info Preview Ringkas -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-5">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Nama Kebun</span>
                <p class="text-sm font-extrabold text-slate-800 mt-1">{{ $kebun->nama }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Lokasi / Wilayah</span>
                <p class="text-sm font-bold text-slate-800 mt-1">{{ $kebun->lokasi_text }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="text-[11px] font-bold text-slate-400 uppercase">Koordinat GPS</span>
                <p class="text-sm font-bold text-slate-800 mt-1 font-mono text-xs">{{ $kebun->latitude }}, {{ $kebun->longitude }}</p>
            </div>
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100">
                <span class="text-[11px] font-bold text-emerald-700 uppercase">Radius Geofence</span>
                <p class="text-sm font-extrabold text-emerald-800 mt-1">± {{ $kebun->radius_meter }} Meter</p>
            </div>
        </div>

        <!-- Form Edit Kebun (Toggleable) -->
        <form id="formEditKebun" action="{{ route('admin.master.kebun.update', $kebun->id) }}" method="POST" class="hidden mt-6 pt-5 border-t border-slate-100 space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Kebun</label>
                    <input type="text" name="nama" value="{{ old('nama', $kebun->nama) }}" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Lokasi / Wilayah</label>
                    <input type="text" name="lokasi_text" value="{{ old('lokasi_text', $kebun->lokasi_text) }}" required 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Luas Lahan</label>
                    <input type="text" name="luas_lahan" value="{{ old('luas_lahan', $kebun->luas_lahan) }}" 
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10" placeholder="Contoh: 2 Hektar">
                </div>

                <!-- Interactive Map Pin Picker for Kebun -->
                <div class="md:col-span-3 space-y-1.5 pt-1">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold text-slate-700 uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-map-location-dot text-emerald-600"></i>
                            <span>Titik Sentral Kebun & Radius Geofence (Geser Pin pada Peta)</span>
                        </label>
                        <span class="text-[11px] text-slate-400">Klik peta atau geser pin hijau untuk menentukan titik tengah kebun</span>
                    </div>
                    <div id="mapPickerKebun" class="w-full h-56 rounded-2xl border border-slate-200 overflow-hidden relative shadow-inner bg-slate-100 z-10"></div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Latitude (Garis Lintang)</label>
                    <input type="number" step="0.0000001" id="kebunLatitude" name="latitude" value="{{ old('latitude', $kebun->latitude) }}" required oninput="onKebunCoordInput()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Longitude (Garis Bujur)</label>
                    <input type="number" step="0.0000001" id="kebunLongitude" name="longitude" value="{{ old('longitude', $kebun->longitude) }}" required oninput="onKebunCoordInput()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Radius Toleransi GPS (Meter)</label>
                    <input type="number" id="kebunRadius" name="radius_meter" value="{{ old('radius_meter', $kebun->radius_meter) }}" required min="10" max="5000" oninput="onKebunRadiusInput()"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
            </div>

            <input type="hidden" name="status" value="aktif">

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="toggleEditKebunForm()"
                    class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" 
                    class="px-5 py-2 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white shadow-sm transition">
                    Simpan Perubahan Kebun
                </button>
            </div>
        </form>
    </div>

    <!-- Card 2: Daftar Blok Lahan (Tabel CRUD) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-agri-600"></i>
                    Daftar Blok Lahan Kebun
                </h3>
                <p class="text-xs text-slate-500">Kelola blok tanaman, jumlah pohon, kondisi kesehatan, dan koordinat blok</p>
            </div>
            <div>
                <button type="button" onclick="openAddBlokModal()" 
                    class="px-4 py-2.5 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white shadow-sm transition flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Blok Baru</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Kode & Nama Blok</th>
                        <th class="p-4 text-center">Status Kondisi</th>
                        <th class="p-4 text-center">Total Tanaman</th>
                        <th class="p-4 text-center">Masalah / Hama</th>
                        <th class="p-4">Koordinat GPS</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bloks as $blok)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-800 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $blok->status_kondisi === 'normal' ? 'bg-emerald-500' : ($blok->status_kondisi === 'perhatian' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                    <span>{{ $blok->kode_blok }}</span>
                                </div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $blok->nama_blok }}</div>
                            </td>
                            <td class="p-4 text-center">
                                @if($blok->status_kondisi === 'normal')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Normal</span>
                                @elseif($blok->status_kondisi === 'perhatian')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">Perhatian</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Masalah</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-extrabold text-slate-800">
                                {{ number_format($blok->jumlah_tanaman) }} <span class="text-xs font-normal text-slate-400">Pohon</span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="text-xs">
                                    <span class="{{ $blok->jumlah_hama > 0 ? 'text-amber-700 font-bold' : 'text-slate-400' }}">{{ $blok->jumlah_hama }} Hama</span>
                                    •
                                    <span class="{{ $blok->jumlah_masalah > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }}">{{ $blok->jumlah_masalah }} Masalah</span>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-xs text-slate-500">
                                <div>{{ $blok->latitude ?: '-' }},</div>
                                <div>{{ $blok->longitude ?: '-' }}</div>
                            </td>
                            <td class="p-4 text-xs text-slate-500 max-w-xs truncate">
                                {{ $blok->keterangan ?: '-' }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick="openEditBlokModal({{ json_encode($blok) }})" title="Edit Blok Lahan"
                                        class="p-2 rounded-xl hover:bg-agri-50 text-agri-700 transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.master.blok.destroy', $blok->id) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $blok->kode_blok }}? Data laporan terkait akan terhapus.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Blok Lahan" class="p-2 rounded-xl hover:bg-rose-50 text-rose-600 transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 text-xs">
                                Belum ada data blok lahan. Silakan klik "Tambah Blok Baru".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL TAMBAH BLOK DENGAN PIN MAP PICKER -->
<!-- ============================================================== -->
<div id="modalAddBlok" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 my-8" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-agri-100 text-agri-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-slate-800">Tambah Blok Lahan Baru</h4>
                    <p class="text-[11px] text-slate-400">Atur posisi blok langsung dengan pin di peta satelit</p>
                </div>
            </div>
            <button onclick="closeAddBlokModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.master.blok.store') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="kebun_id" value="{{ $kebun->id }}">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Blok *</label>
                    <input type="text" name="kode_blok" required placeholder="Contoh: Blok C"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Kondisi Awal</label>
                    <select name="status_kondisi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600">
                        <option value="normal">Normal (Subur)</option>
                        <option value="perhatian">Perlu Perhatian</option>
                        <option value="masalah">Ada Masalah</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama / Keterangan Varietas *</label>
                <input type="text" name="nama_blok" required placeholder="Contoh: Blok C - Cabai Rawit Hiyung"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Pohon</label>
                    <input type="number" name="jumlah_tanaman" value="100" required min="1"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Hama</label>
                    <input type="number" name="jumlah_hama" value="0" min="0"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Masalah</label>
                    <input type="number" name="jumlah_masalah" value="0" min="0"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
            </div>

            <!-- PIN MAP PICKER: TAMBAH BLOK -->
            <div class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 uppercase flex items-center gap-1.5">
                        <i class="fa-solid fa-map-pin text-rose-500"></i>
                        <span>Tentukan Posisi Blok pada Peta Satelit</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Klik peta atau geser pin merah</span>
                </div>
                
                <div id="mapPickerAddBlok" class="w-full h-56 rounded-2xl border border-slate-200 overflow-hidden relative shadow-inner bg-slate-100 z-10"></div>
                
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-0.5">Latitude</label>
                        <input type="number" step="0.0000001" id="addLatitude" name="latitude" value="{{ $kebun->latitude }}" oninput="onAddBlokCoordInput()"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:border-agri-600">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-0.5">Longitude</label>
                        <input type="number" step="0.0000001" id="addLongitude" name="longitude" value="{{ $kebun->longitude }}" oninput="onAddBlokCoordInput()"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:border-agri-600">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea name="keterangan" rows="2" placeholder="Catatan kondisi bedengan, mulsa, atau jarak tanam..."
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeAddBlokModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm transition">Simpan Blok</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL EDIT BLOK DENGAN PIN MAP PICKER -->
<!-- ============================================================== -->
<div id="modalEditBlok" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 my-8" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm">
                    <i class="fa-solid fa-pen-to-square"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-slate-800">Edit Blok Lahan</h4>
                    <p class="text-[11px] text-slate-400">Geser pin pada peta satelit untuk memindahkan titik koordinat blok</p>
                </div>
            </div>
            <button onclick="closeEditBlokModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="formEditBlokAction" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Blok *</label>
                    <input type="text" id="editKodeBlok" name="kode_blok" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status Kondisi</label>
                    <select id="editStatusKondisi" name="status_kondisi" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600">
                        <option value="normal">Normal (Subur)</option>
                        <option value="perhatian">Perlu Perhatian</option>
                        <option value="masalah">Ada Masalah</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama / Varietas *</label>
                <input type="text" id="editNamaBlok" name="nama_blok" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Pohon</label>
                    <input type="number" id="editJumlahTanaman" name="jumlah_tanaman" required min="1"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Hama</label>
                    <input type="number" id="editJumlahHama" name="jumlah_hama" min="0"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jml Masalah</label>
                    <input type="number" id="editJumlahMasalah" name="jumlah_masalah" min="0"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                </div>
            </div>

            <!-- PIN MAP PICKER: EDIT BLOK -->
            <div class="space-y-1.5 pt-1">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700 uppercase flex items-center gap-1.5">
                        <i class="fa-solid fa-map-pin text-rose-500"></i>
                        <span>Titik Pin Lokasi Blok di Peta Satelit</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Klik peta atau geser pin merah</span>
                </div>
                
                <div id="mapPickerEditBlok" class="w-full h-56 rounded-2xl border border-slate-200 overflow-hidden relative shadow-inner bg-slate-100 z-10"></div>
                
                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-0.5">Latitude</label>
                        <input type="number" step="0.0000001" id="editLatitude" name="latitude" oninput="onEditBlokCoordInput()"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:border-agri-600">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase mb-0.5">Longitude</label>
                        <input type="number" step="0.0000001" id="editLongitude" name="longitude" oninput="onEditBlokCoordInput()"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-mono focus:border-agri-600">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea id="editKeterangan" name="keterangan" rows="2"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeEditBlokModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm transition">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@if(!empty($googleMapsApiKey))
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=onGoogleMapsReady" async defer></script>
@endif

<script>
    const defaultKebunLat = {{ $kebun->latitude ?? -0.1234000 }};
    const defaultKebunLng = {{ $kebun->longitude ?? 109.3456000 }};
    const defaultRadius = {{ $kebun->radius_meter ?? 50 }};
    const googleApiKey = "{{ $googleMapsApiKey }}";

    let googleMapsReady = false;
    window.onGoogleMapsReady = function () {
        googleMapsReady = true;
    };

    window.gm_authFailure = function () {
        console.warn("Google Maps Auth gagal di Master Lahan. Menggunakan Leaflet Satelit.");
        googleMapsReady = false;
    };

    // Objek Picker Manager
    let pickerEditBlok = null;
    let pickerAddBlok = null;
    let pickerKebun = null;

    /**
     * Factory function untuk membuat Universal Interactive Pin Map Picker
     * Mendukung Google Maps (Hybrid) & Auto-fallback Leaflet Esri Satellite
     */
    function initInteractiveMapPicker(containerId, initialLat, initialLng, onLocationChange, isKebun = false, initialRadius = 50) {
        const container = document.getElementById(containerId);
        if (!container) return null;
        container.innerHTML = '';

        let useGoogle = googleMapsReady && window.google && window.google.maps;

        if (useGoogle) {
            try {
                const map = new google.maps.Map(container, {
                    center: { lat: initialLat, lng: initialLng },
                    zoom: 18,
                    mapTypeId: 'hybrid',
                    streetViewControl: false,
                    mapTypeControl: false,
                    fullscreenControl: false,
                });

                const pinIcon = isKebun 
                    ? 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png';

                const marker = new google.maps.Marker({
                    position: { lat: initialLat, lng: initialLng },
                    map: map,
                    draggable: true,
                    title: isKebun ? 'Pusat Kebun (Geser)' : 'Titik Blok (Geser)',
                    icon: { url: pinIcon }
                });

                let circle = null;
                if (isKebun) {
                    circle = new google.maps.Circle({
                        center: { lat: initialLat, lng: initialLng },
                        radius: initialRadius,
                        map: map,
                        strokeColor: '#16a34a',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#22c55e',
                        fillOpacity: 0.25,
                    });
                }

                // Drag Marker Event
                marker.addListener('dragend', function (e) {
                    const lat = e.latLng.lat();
                    const lng = e.latLng.lng();
                    if (circle) circle.setCenter(e.latLng);
                    onLocationChange(lat, lng);
                });

                // Click on Map to Move Marker
                map.addListener('click', function (e) {
                    marker.setPosition(e.latLng);
                    if (circle) circle.setCenter(e.latLng);
                    onLocationChange(e.latLng.lat(), e.latLng.lng());
                });

                return {
                    engine: 'google',
                    map: map,
                    marker: marker,
                    circle: circle,
                    setCoordinates: function (lat, lng) {
                        const pos = new google.maps.LatLng(lat, lng);
                        marker.setPosition(pos);
                        map.panTo(pos);
                        if (circle) circle.setCenter(pos);
                    },
                    setRadius: function (r) {
                        if (circle) circle.setRadius(parseFloat(r) || 50);
                    },
                    resize: function () {
                        google.maps.event.trigger(map, 'resize');
                        map.setCenter(marker.getPosition());
                    }
                };
            } catch (err) {
                console.warn("Gagal init Google Map picker, fallback ke Leaflet:", err);
            }
        }

        // ==========================================
        // LEAFLET SATELIT ENGINE (ESRI WORLD IMAGERY)
        // ==========================================
        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: '&copy; Esri World Imagery',
            maxZoom: 19
        });

        const map = L.map(containerId, {
            center: [initialLat, initialLng],
            zoom: 18,
            layers: [satelliteLayer],
            attributionControl: false
        });

        const pinColor = isKebun ? '#16a34a' : '#ef4444';
        const pinIconHtml = `<div style="background: ${pinColor}; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 15px; border: 2.5px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.4); cursor: grab;">${isKebun ? '🌱' : '📍'}</div>`;

        const customIcon = L.divIcon({
            className: 'custom-picker-pin',
            html: pinIconHtml,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const marker = L.marker([initialLat, initialLng], {
            icon: customIcon,
            draggable: true
        }).addTo(map);

        let circle = null;
        if (isKebun) {
            circle = L.circle([initialLat, initialLng], {
                color: '#16a34a',
                fillColor: '#22c55e',
                fillOpacity: 0.25,
                radius: initialRadius,
                weight: 2
            }).addTo(map);
        }

        // Marker Drag Event
        marker.on('dragend', function () {
            const pos = marker.getLatLng();
            if (circle) circle.setLatLng(pos);
            onLocationChange(pos.lat, pos.lng);
        });

        // Click on Map to Move Marker
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            if (circle) circle.setLatLng(e.latlng);
            onLocationChange(e.latlng.lat, e.latlng.lng);
        });

        return {
            engine: 'leaflet',
            map: map,
            marker: marker,
            circle: circle,
            setCoordinates: function (lat, lng) {
                marker.setLatLng([lat, lng]);
                map.panTo([lat, lng]);
                if (circle) circle.setLatLng([lat, lng]);
            },
            setRadius: function (r) {
                if (circle) circle.setRadius(parseFloat(r) || 50);
            },
            resize: function () {
                map.invalidateSize();
                map.panTo(marker.getLatLng());
            }
        };
    }

    // ==========================================
    // 1. MODAL EDIT BLOK HANDLERS
    // ==========================================
    function openEditBlokModal(blok) {
        document.getElementById('formEditBlokAction').action = `/admin/master/lahan/blok/${blok.id}`;
        document.getElementById('editKodeBlok').value = blok.kode_blok;
        document.getElementById('editNamaBlok').value = blok.nama_blok || '';
        document.getElementById('editStatusKondisi').value = blok.status_kondisi;
        document.getElementById('editJumlahTanaman').value = blok.jumlah_tanaman;
        document.getElementById('editJumlahHama').value = blok.jumlah_hama || 0;
        document.getElementById('editJumlahMasalah').value = blok.jumlah_masalah || 0;
        document.getElementById('editKeterangan').value = blok.keterangan || '';

        const lat = blok.latitude ? parseFloat(blok.latitude) : defaultKebunLat;
        const lng = blok.longitude ? parseFloat(blok.longitude) : defaultKebunLng;

        document.getElementById('editLatitude').value = lat.toFixed(7);
        document.getElementById('editLongitude').value = lng.toFixed(7);

        document.getElementById('modalEditBlok').classList.remove('hidden');

        // Render atau update Pin Map Picker
        setTimeout(function () {
            if (!pickerEditBlok) {
                pickerEditBlok = initInteractiveMapPicker('mapPickerEditBlok', lat, lng, function (newLat, newLng) {
                    document.getElementById('editLatitude').value = newLat.toFixed(7);
                    document.getElementById('editLongitude').value = newLng.toFixed(7);
                }, false);
            } else {
                pickerEditBlok.setCoordinates(lat, lng);
                pickerEditBlok.resize();
            }
        }, 150);
    }

    function closeEditBlokModal() {
        document.getElementById('modalEditBlok').classList.add('hidden');
    }

    function onEditBlokCoordInput() {
        if (!pickerEditBlok) return;
        const lat = parseFloat(document.getElementById('editLatitude').value);
        const lng = parseFloat(document.getElementById('editLongitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            pickerEditBlok.setCoordinates(lat, lng);
        }
    }

    // ==========================================
    // 2. MODAL TAMBAH BLOK HANDLERS
    // ==========================================
    function openAddBlokModal() {
        document.getElementById('modalAddBlok').classList.remove('hidden');

        const lat = defaultKebunLat;
        const lng = defaultKebunLng;
        document.getElementById('addLatitude').value = lat.toFixed(7);
        document.getElementById('addLongitude').value = lng.toFixed(7);

        setTimeout(function () {
            if (!pickerAddBlok) {
                pickerAddBlok = initInteractiveMapPicker('mapPickerAddBlok', lat, lng, function (newLat, newLng) {
                    document.getElementById('addLatitude').value = newLat.toFixed(7);
                    document.getElementById('addLongitude').value = newLng.toFixed(7);
                }, false);
            } else {
                pickerAddBlok.setCoordinates(lat, lng);
                pickerAddBlok.resize();
            }
        }, 150);
    }

    function closeAddBlokModal() {
        document.getElementById('modalAddBlok').classList.add('hidden');
    }

    function onAddBlokCoordInput() {
        if (!pickerAddBlok) return;
        const lat = parseFloat(document.getElementById('addLatitude').value);
        const lng = parseFloat(document.getElementById('addLongitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            pickerAddBlok.setCoordinates(lat, lng);
        }
    }

    // ==========================================
    // 3. FORM EDIT KEBUN SENTRAL HANDLERS
    // ==========================================
    function toggleEditKebunForm() {
        const form = document.getElementById('formEditKebun');
        form.classList.toggle('hidden');

        if (!form.classList.contains('hidden')) {
            const lat = parseFloat(document.getElementById('kebunLatitude').value) || defaultKebunLat;
            const lng = parseFloat(document.getElementById('kebunLongitude').value) || defaultKebunLng;
            const rad = parseFloat(document.getElementById('kebunRadius').value) || defaultRadius;

            setTimeout(function () {
                if (!pickerKebun) {
                    pickerKebun = initInteractiveMapPicker('mapPickerKebun', lat, lng, function (newLat, newLng) {
                        document.getElementById('kebunLatitude').value = newLat.toFixed(7);
                        document.getElementById('kebunLongitude').value = newLng.toFixed(7);
                    }, true, rad);
                } else {
                    pickerKebun.setCoordinates(lat, lng);
                    pickerKebun.setRadius(rad);
                    pickerKebun.resize();
                }
            }, 150);
        }
    }

    function onKebunCoordInput() {
        if (!pickerKebun) return;
        const lat = parseFloat(document.getElementById('kebunLatitude').value);
        const lng = parseFloat(document.getElementById('kebunLongitude').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            pickerKebun.setCoordinates(lat, lng);
        }
    }

    function onKebunRadiusInput() {
        if (!pickerKebun) return;
        const rad = parseFloat(document.getElementById('kebunRadius').value);
        if (!isNaN(rad)) {
            pickerKebun.setRadius(rad);
        }
    }
</script>
@endpush
@endsection
