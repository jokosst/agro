@extends('admin.layout')

@section('title', 'Master Data Pekerja')
@section('page_title', 'Master Data Pekerja & Pengguna')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-users-gear text-agri-600"></i>
                Kelola Data Pekerja Lapangan & Administrator
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">Tambah akun pekerja baru untuk aplikasi mobile, ubah data profil, atau reset kata sandi</p>
        </div>
        
        <div>
            <button type="button" onclick="openAddPekerjaModal()"
                class="px-4 py-2.5 rounded-xl text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white shadow-sm transition flex items-center gap-2">
                <i class="fa-solid fa-user-plus"></i>
                <span>Tambah Pekerja Baru</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.master.pekerja') }}" class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
            <div class="relative flex-1 sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email..." 
                    class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>

            <select name="role" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 font-medium">
                <option value="">Semua Peran (Role)</option>
                <option value="pekerja" {{ request('role') === 'pekerja' ? 'selected' : '' }}>Pekerja Lapangan</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
            </select>

            <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                Filter
            </button>

            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.master.pekerja') }}" class="text-xs text-rose-600 hover:underline px-2 py-1">Reset</a>
            @endif
        </form>

        <div class="text-xs text-slate-500 font-medium">
            Total: <span class="font-extrabold text-slate-800">{{ $pekerjaList->total() }}</span> Akun Pengguna
        </div>
    </div>

    <!-- Table Data Pekerja -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="p-4">Pekerja / Pengguna</th>
                        <th class="p-4">Username & Email</th>
                        <th class="p-4">No. Telepon / WhatsApp</th>
                        <th class="p-4 text-center">Peran (Role)</th>
                        <th class="p-4">Penugasan Kebun</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pekerjaList as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl {{ $p->role === 'admin' ? 'bg-amber-600' : 'bg-agri-700' }} text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ strtoupper(substr($p->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-800">{{ $p->name }}</div>
                                        <span class="text-[11px] text-slate-400">ID #{{ $p->id }} • Terdaftar {{ $p->created_at ? $p->created_at->translatedFormat('d M Y') : '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-slate-700 font-mono text-xs">@ {{ $p->username }}</div>
                                <div class="text-xs text-slate-500">{{ $p->email }}</div>
                            </td>
                            <td class="p-4 text-xs font-semibold text-slate-700">
                                {{ $p->phone ?: '-' }}
                            </td>
                            <td class="p-4 text-center">
                                @if($p->role === 'admin')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <i class="fa-solid fa-user-shield text-[10px] mr-1"></i> Admin
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <i class="fa-solid fa-person-digging text-[10px] mr-1"></i> Pekerja
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-xs text-slate-700">
                                <i class="fa-solid fa-location-dot text-agri-600 mr-1"></i>
                                {{ $p->kebun->nama ?? 'Kebun Cabai Agrocom' }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.detail_pekerja', $p->id) }}" 
                                        class="p-2 rounded-lg text-slate-600 hover:text-agri-700 hover:bg-emerald-50 transition" title="Lihat Rekap Kerja">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <button type="button" onclick='openEditPekerjaModal(@json($p))'
                                        class="p-2 rounded-lg text-slate-600 hover:text-agri-700 hover:bg-emerald-50 transition" title="Edit Data">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" onclick='openResetPasswordModal({{ $p->id }}, "{{ $p->name }}")'
                                        class="p-2 rounded-lg text-amber-600 hover:text-amber-800 hover:bg-amber-50 transition" title="Reset Password">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                    @if($p->id !== auth()->id())
                                        <form action="{{ route('admin.master.pekerja.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Hapus pekerja {{ $p->name }}? Seluruh riwayat absensi dan laporannya akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Akun">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-400">
                                Data pekerja tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pekerjaList->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $pekerjaList->links() }}
            </div>
        @endif
    </div>

</div>

<!-- MODAL TAMBAH PEKERJA -->
<div id="modalAddPekerja" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Tambah Pekerja / Pengguna Baru</h4>
            <button onclick="closeAddPekerjaModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('admin.master.pekerja.store') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username Login *</label>
                    <input type="text" name="username" required placeholder="budi"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role) *</label>
                    <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                        <option value="pekerja">Pekerja Lapangan (Mobile App)</option>
                        <option value="admin">Administrator (Web Panel)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Penugasan Kebun</label>
                <select name="kebun_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                    @foreach($kebunList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email *</label>
                    <input type="email" name="email" required placeholder="budi@agrocom.id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="phone" placeholder="081234567890"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kata Sandi Awal (Password) *</label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeAddPekerjaModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Pekerja</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PEKERJA -->
<div id="modalEditPekerja" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Edit Data Pekerja</h4>
            <button onclick="closeEditPekerjaModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formEditPekerjaAction" method="POST" class="space-y-3.5">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap *</label>
                <input type="text" id="editName" name="name" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Username Login *</label>
                    <input type="text" id="editUsername" name="username" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Peran (Role) *</label>
                    <select id="editRole" name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                        <option value="pekerja">Pekerja Lapangan (Mobile App)</option>
                        <option value="admin">Administrator (Web Panel)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Penugasan Kebun</label>
                <select id="editKebunId" name="kebun_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold">
                    @foreach($kebunList as $k)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Alamat Email *</label>
                    <input type="email" id="editEmail" name="email" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">No. HP / WhatsApp</label>
                    <input type="text" id="editPhone" name="phone"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Ganti Kata Sandi (Kosongkan bila tidak ingin diubah)</label>
                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak diubah"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditPekerjaModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-agri-700 hover:bg-agri-800 text-white rounded-xl shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL RESET PASSWORD -->
<div id="modalResetPassword" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <h4 class="font-bold text-base text-slate-800">Reset Kata Sandi</h4>
            <button onclick="closeResetPasswordModal()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="formResetPasswordAction" method="POST" class="space-y-3.5">
            @csrf
            <p class="text-xs text-slate-500">
                Atur kata sandi baru untuk akun <strong id="resetTargetName" class="text-slate-800"></strong>:
            </p>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password Baru *</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-agri-600 focus:ring-2 focus:ring-agri-600/10">
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeResetPasswordModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-sm">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddPekerjaModal() {
        document.getElementById('modalAddPekerja').classList.remove('hidden');
    }
    function closeAddPekerjaModal() {
        document.getElementById('modalAddPekerja').classList.add('hidden');
    }
    function openEditPekerjaModal(pekerja) {
        document.getElementById('formEditPekerjaAction').action = `/admin/master/pekerja/${pekerja.id}`;
        document.getElementById('editName').value = pekerja.name;
        document.getElementById('editUsername').value = pekerja.username;
        document.getElementById('editEmail').value = pekerja.email;
        document.getElementById('editPhone').value = pekerja.phone || '';
        document.getElementById('editRole').value = pekerja.role;
        if (document.getElementById('editKebunId')) {
            document.getElementById('editKebunId').value = pekerja.kebun_id || '';
        }
        document.getElementById('modalEditPekerja').classList.remove('hidden');
    }
    function closeEditPekerjaModal() {
        document.getElementById('modalEditPekerja').classList.add('hidden');
    }
    function openResetPasswordModal(id, name) {
        document.getElementById('formResetPasswordAction').action = `/admin/master/pekerja/${id}/reset-password`;
        document.getElementById('resetTargetName').innerText = name;
        document.getElementById('modalResetPassword').classList.remove('hidden');
    }
    function closeResetPasswordModal() {
        document.getElementById('modalResetPassword').classList.add('hidden');
    }
</script>
@endsection
