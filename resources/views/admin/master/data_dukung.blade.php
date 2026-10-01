@extends('admin.layout')

@section('title', 'Master Data Dukung Hama & Penyakit')
@section('page_title', 'Master Data Dukung (Hama, Penyakit & Gulma)')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-shield-virus text-agri-600"></i>
                Katalog Master Hama, Penyakit & Gulma Cabai
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Basis data referensi lapangan untuk gejala serangan, bagian tanaman, dan rekomendasi solusi pengendalian</p>
        </div>

        <div>
            <button type="button" onclick="openAddDataModal()"
                class="px-4 py-2.5 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Data Dukung Baru</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.master.data_dukung') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, gejala, penanganan..." 
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>

            <select name="kategori" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium">
                <option value="">Semua Kategori</option>
                <option value="Hama" {{ request('kategori') === 'Hama' ? 'selected' : '' }}>Hama</option>
                <option value="Penyakit" {{ request('kategori') === 'Penyakit' ? 'selected' : '' }}>Penyakit</option>
                <option value="Gulma" {{ request('kategori') === 'Gulma' ? 'selected' : '' }}>Gulma</option>
            </select>

            <select name="bagian" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium">
                <option value="">Semua Bagian Tanaman</option>
                <option value="Daun" {{ request('bagian') === 'Daun' ? 'selected' : '' }}>Daun</option>
                <option value="Batang" {{ request('bagian') === 'Batang' ? 'selected' : '' }}>Batang</option>
                <option value="Bunga" {{ request('bagian') === 'Bunga' ? 'selected' : '' }}>Bunga</option>
                <option value="Buah" {{ request('bagian') === 'Buah' ? 'selected' : '' }}>Buah</option>
                <option value="Semua" {{ request('bagian') === 'Semua' ? 'selected' : '' }}>Semua Bagian</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Filter
            </button>

            @if(request()->hasAny(['search', 'kategori', 'bagian']))
                <a href="{{ route('admin.master.data_dukung') }}" class="text-xs text-rose-600 hover:underline px-2 py-1">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium">
            Total: <span class="font-extrabold text-slate-800">{{ $items->total() }}</span> Katalog
        </div>
    </div>

    <!-- Table Master Data Dukung -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Nama Hama / Penyakit</th>
                        <th class="p-4 text-center">Kategori</th>
                        <th class="p-4 text-center">Bagian Tanaman</th>
                        <th class="p-4">Gejala Kerusakan</th>
                        <th class="p-4">Rekomendasi Penanganan</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="font-extrabold text-slate-800">{{ $item->nama }}</div>
                                <span class="text-[11px] text-slate-400">ID #{{ $item->id }}</span>
                            </td>
                            <td class="p-4 text-center">
                                @if($item->kategori === 'Hama')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-bug text-[10px] mr-1"></i> Hama
                                    </span>
                                @elseif($item->kategori === 'Penyakit')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        <i class="fa-solid fa-virus text-[10px] mr-1"></i> Penyakit
                                    </span>
                                @elseif($item->kategori === 'Gulma')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-seedling text-[10px] mr-1"></i> Gulma
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ $item->kategori }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $item->bagian_tanaman }}
                                </span>
                            </td>
                            <td class="p-4 max-w-xs text-xs text-slate-600">
                                {{ $item->gejala ?: '-' }}
                            </td>
                            <td class="p-4 max-w-xs text-xs text-slate-700">
                                {{ $item->solusi_pengendalian ?: '-' }}
                            </td>
                            <td class="p-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $item->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick='openEditDataModal(@json($item))'
                                        class="p-2 rounded-lg text-slate-600 hover:text-agri-700 hover:bg-emerald-50 transition" title="Edit Data">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.master.data_dukung.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus master data {{ $item->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Data">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-400">
                                Data dukung hama & penyakit belum ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $items->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL TAMBAH DATA DUKUNG -->
<div id="modalAddData" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Tambah Data Dukung Baru</h4>
            <button onclick="closeAddDataModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('admin.master.data_dukung.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Hama / Penyakit *</label>
                <input type="text" name="nama" required placeholder="Contoh: Kutu Daun Persik (Myzus persicae)"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori *</label>
                    <select name="kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                        <option value="Hama">Hama Tanaman</option>
                        <option value="Penyakit">Penyakit Tanaman</option>
                        <option value="Gulma">Gulma / Rumput Liar</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bagian Tanaman *</label>
                    <select name="bagian_tanaman" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="Daun">Daun</option>
                        <option value="Batang">Batang</option>
                        <option value="Bunga">Bunga</option>
                        <option value="Buah">Buah</option>
                        <option value="Akar">Akar</option>
                        <option value="Semua">Semua Bagian</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gejala Kerusakan</label>
                <textarea name="gejala" rows="2" placeholder="Deskripsi tanda-tanda visual yang muncul pada tanaman..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rekomendasi Tindakan & Solusi</label>
                <textarea name="solusi_pengendalian" rows="2" placeholder="Petunjuk teknis pengendalian hayati, kimiawi, atau sanitasi..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                    <option value="aktif">Aktif (Tampil sebagai Pilihan Laporan)</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAddDataModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Data Dukung</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT DATA DUKUNG -->
<div id="modalEditData" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Edit Data Dukung</h4>
            <button onclick="closeEditDataModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formEditDataAction" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Hama / Penyakit *</label>
                <input type="text" id="editNama" name="nama" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kategori *</label>
                    <select id="editKategori" name="kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                        <option value="Hama">Hama Tanaman</option>
                        <option value="Penyakit">Penyakit Tanaman</option>
                        <option value="Gulma">Gulma / Rumput Liar</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Bagian Tanaman *</label>
                    <select id="editBagian" name="bagian_tanaman" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                        <option value="Daun">Daun</option>
                        <option value="Batang">Batang</option>
                        <option value="Bunga">Bunga</option>
                        <option value="Buah">Buah</option>
                        <option value="Akar">Akar</option>
                        <option value="Semua">Semua Bagian</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Gejala Kerusakan</label>
                <textarea id="editGejala" name="gejala" rows="2"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rekomendasi Tindakan & Solusi</label>
                <textarea id="editSolusi" name="solusi_pengendalian" rows="2"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Status</label>
                <select id="editStatus" name="status" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditDataModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddDataModal() {
        document.getElementById('modalAddData').classList.remove('hidden');
    }
    function closeAddDataModal() {
        document.getElementById('modalAddData').classList.add('hidden');
    }
    function openEditDataModal(item) {
        document.getElementById('formEditDataAction').action = `/admin/master/data-dukung/${item.id}`;
        document.getElementById('editNama').value = item.nama;
        document.getElementById('editKategori').value = item.kategori;
        document.getElementById('editBagian').value = item.bagian_tanaman;
        document.getElementById('editGejala').value = item.gejala || '';
        document.getElementById('editSolusi').value = item.solusi_pengendalian || '';
        document.getElementById('editStatus').value = item.status;
        document.getElementById('modalEditData').classList.remove('hidden');
    }
    function closeEditDataModal() {
        document.getElementById('modalEditData').classList.add('hidden');
    }
</script>
@endsection
