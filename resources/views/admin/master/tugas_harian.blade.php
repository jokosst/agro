@extends('admin.layout')

@section('title', 'Master Tugas Harian')
@section('page_title', 'Master Tugas & Checklist Pekerjaan Harian')

@section('content')
<div class="space-y-6">

    <!-- Header Action -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-agri-600"></i>
                Daftar Checklist Tugas Harian Pekerja
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Daftar tugas ini otomatis muncul pada aplikasi mobile pekerja setiap hari untuk dicentang</p>
        </div>

        <div>
            <button type="button" onclick="openAddTugasModal()"
                class="px-4 py-2.5 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Tugas Baru</span>
            </button>
        </div>
    </div>

    <!-- Table Master Tugas -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4 text-center w-16">Urutan</th>
                        <th class="p-4">Nama Tugas / Pekerjaan</th>
                        <th class="p-4">Petunjuk Pelaksanaan</th>
                        <th class="p-4 text-center">Status Muncul di App</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tugasList as $tugas)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4 text-center font-extrabold text-slate-500">
                                <span class="w-7 h-7 rounded-lg bg-slate-100 inline-flex items-center justify-center text-xs font-mono font-bold text-slate-700">
                                    {{ $tugas->urutan }}
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800 flex items-center gap-2.5">
                                    <span class="w-2 h-2 rounded-full {{ $tugas->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                    <span>{{ $tugas->judul }}</span>
                                </div>
                            </td>
                            <td class="p-4 text-xs text-slate-500 max-w-md">
                                {{ $tugas->deskripsi ?: 'Tugas rutin checklist harian.' }}
                            </td>
                            <td class="p-4 text-center">
                                <form action="{{ route('admin.master.tugas_harian.toggle', $tugas->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" 
                                        class="px-3 py-1 rounded-full text-xs font-bold transition flex items-center gap-1.5 mx-auto {{ $tugas->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                                        <i class="fa-solid {{ $tugas->is_active ? 'fa-circle-check text-emerald-600' : 'fa-circle-xmark text-slate-400' }}"></i>
                                        <span>{{ $tugas->is_active ? 'Aktif di Mobile' : 'Dinonaktifkan' }}</span>
                                    </button>
                                </form>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" onclick='openEditTugasModal(@json($tugas))'
                                        class="p-2 rounded-lg text-slate-600 hover:text-agri-700 hover:bg-emerald-50 transition" title="Edit Tugas">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <form action="{{ route('admin.master.tugas_harian.destroy', $tugas->id) }}" method="POST" onsubmit="return confirm('Hapus tugas {{ $tugas->judul }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Tugas">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-400">
                                Belum ada tugas harian yang dikonfigurasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL TAMBAH TUGAS -->
<div id="modalAddTugas" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Tambah Tugas Harian Baru</h4>
            <button onclick="closeAddTugasModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('admin.master.tugas_harian.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul / Nama Tugas *</label>
                <input type="text" name="judul" required placeholder="Contoh: Pengocoran pupuk NPK"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Urut Tampilan</label>
                <input type="number" name="urutan" value="{{ count($tugasList) + 1 }}" min="1"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi / Petunjuk</label>
                <textarea name="deskripsi" rows="2" placeholder="Petunjuk dosis, area atau cara eksekusi tugas..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="isActiveAdd" name="is_active" value="1" checked class="w-4 h-4 rounded text-agri-600">
                <label for="isActiveAdd" class="text-xs font-medium text-slate-700">Aktifkan langsung agar tampil di aplikasi mobile</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAddTugasModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Tugas</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT TUGAS -->
<div id="modalEditTugas" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Edit Tugas Harian</h4>
            <button onclick="closeEditTugasModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formEditTugasAction" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul / Nama Tugas *</label>
                <input type="text" id="editJudul" name="judul" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nomor Urut Tampilan</label>
                <input type="number" id="editUrutan" name="urutan" min="1"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi / Petunjuk</label>
                <textarea id="editDeskripsi" name="deskripsi" rows="2"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea>
            </div>
            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" id="editIsActive" name="is_active" value="1" class="w-4 h-4 rounded text-agri-600">
                <label for="editIsActive" class="text-xs font-medium text-slate-700">Aktifkan tugas di mobile app</label>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditTugasModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddTugasModal() {
        document.getElementById('modalAddTugas').classList.remove('hidden');
    }
    function closeAddTugasModal() {
        document.getElementById('modalAddTugas').classList.add('hidden');
    }
    function openEditTugasModal(tugas) {
        document.getElementById('formEditTugasAction').action = `/admin/master/tugas-harian/${tugas.id}`;
        document.getElementById('editJudul').value = tugas.judul;
        document.getElementById('editUrutan').value = tugas.urutan;
        document.getElementById('editDeskripsi').value = tugas.deskripsi || '';
        document.getElementById('editIsActive').checked = Boolean(tugas.is_active);
        document.getElementById('modalEditTugas').classList.remove('hidden');
    }
    function closeEditTugasModal() {
        document.getElementById('modalEditTugas').classList.add('hidden');
    }
</script>
@endsection
