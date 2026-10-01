@extends('admin.layout')

@section('title', 'Rekap Laporan Harian, Mingguan & Bulanan')
@section('page_title', 'Rekap Laporan Lapangan Pekerja')

@section('content')
<div class="space-y-6">

    <!-- Filter & Periode Bar (Sesuai Storyboard 11) -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="font-extrabold text-lg text-slate-800">Rekap Kegiatan & Produktivitas</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                    Periode: {{ $periodeText }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Laporan harian kondisi tanaman, gulma, pemupukan, penyemprotan, dan foto sebelum/sesudah</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- Period Selector Tabs (Fully Functional) -->
            <div class="bg-slate-100 p-1 rounded-2xl flex items-center gap-1 border border-slate-200">
                <a href="{{ route('admin.laporan_harian', ['period' => 'harian', 'date' => $selectedDate, 'view' => $viewMode]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'harian' ? 'bg-agri-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Harian
                </a>
                <a href="{{ route('admin.laporan_harian', ['period' => 'mingguan', 'date' => $selectedDate, 'view' => $viewMode]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'mingguan' ? 'bg-agri-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Mingguan
                </a>
                <a href="{{ route('admin.laporan_harian', ['period' => 'bulanan', 'date' => $selectedDate, 'view' => $viewMode]) }}" 
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition {{ $period === 'bulanan' ? 'bg-agri-700 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    Bulanan
                </a>
            </div>

            <!-- View Mode Switch: Table vs Cards -->
            <div class="bg-slate-100 p-1 rounded-2xl flex items-center gap-1 border border-slate-200">
                <a href="{{ route('admin.laporan_harian', ['period' => $period, 'date' => $selectedDate, 'view' => 'table']) }}" 
                   title="Tampilan Tabel"
                   class="p-1.5 px-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'table' ? 'bg-white text-agri-800 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa-solid fa-table-list"></i>
                    <span class="hidden sm:inline">Tabel</span>
                </a>
                <a href="{{ route('admin.laporan_harian', ['period' => $period, 'date' => $selectedDate, 'view' => 'cards']) }}" 
                   title="Tampilan Kartu"
                   class="p-1.5 px-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $viewMode === 'cards' ? 'bg-white text-agri-800 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                    <i class="fa-solid fa-grip"></i>
                    <span class="hidden sm:inline">Kartu</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('admin.laporan_harian') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="hidden" name="period" value="{{ $period }}">
            <input type="hidden" name="view" value="{{ $viewMode }}">

            <div class="flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 uppercase">Pilih Tanggal:</label>
                @if($period === 'bulanan')
                    <input type="month" name="date" value="{{ \Carbon\Carbon::parse($selectedDate)->format('Y-m') }}" 
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50">
                @else
                    <input type="date" name="date" value="{{ $selectedDate }}" 
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 bg-slate-50">
                @endif
            </div>

            <div class="flex items-center gap-2">
                <select name="user_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-slate-50">
                    <option value="">Semua Pekerja</option>
                    @foreach($pekerjaList as $pk)
                        <option value="{{ $pk->id }}" {{ request('user_id') == $pk->id ? 'selected' : '' }}>{{ $pk->name }}</option>
                    @endforeach
                </select>
            </div>

            @if(request()->filled('user_id') || $selectedDate !== \Carbon\Carbon::today()->toDateString())
                <a href="{{ route('admin.laporan_harian', ['period' => $period, 'view' => $viewMode]) }}" class="text-xs font-bold text-rose-600 hover:underline">
                    Reset Filter
                </a>
            @endif
        </form>

        <span class="text-xs text-slate-400 font-semibold">
            Ditemukan <strong class="text-slate-800">{{ $laporanList->total() }}</strong> Laporan Lapangan
        </span>
    </div>

    <!-- Cards Rekap KPI (Real Database Calculations) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Total Kehadiran</span>
            <p class="text-2xl font-black text-slate-800 mt-1">
                {{ $totalHadir }} <span class="text-sm font-semibold text-slate-400">/ {{ $totalPekerjaAktif }} Pekerja</span>
            </p>
            <div class="w-full bg-slate-100 h-2 rounded-full mt-3 overflow-hidden">
                @php $presensiPct = $totalPekerjaAktif > 0 ? min(100, round(($totalHadir / $totalPekerjaAktif) * 100)) : 0; @endphp
                <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $presensiPct }}%"></div>
            </div>
            <span class="text-[11px] text-emerald-600 font-bold mt-1.5 block">{{ $presensiPct }}% Hadir Presensi</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Laporan Masuk</span>
            <p class="text-2xl font-black text-slate-800 mt-1">{{ $totalLaporan }}</p>
            <span class="text-[11px] text-emerald-600 font-bold mt-2 flex items-center gap-1">
                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                <span>Tersimpan di Database</span>
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Masalah Tanaman</span>
            <p class="text-2xl font-black {{ $masalahPeriode > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1">
                {{ $masalahPeriode }} Pohon
            </p>
            <span class="text-[11px] {{ $masalahPeriode > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }} mt-2 block">
                {{ $masalahPeriode > 0 ? 'Dalam Pengawasan Kebun' : 'Kondisi Tanaman Bersih' }}
            </span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[11px] text-slate-400 font-bold uppercase tracking-wider block">Pemupukan & Semprot</span>
            <p class="text-2xl font-black text-agri-700 mt-1">
                {{ $pupukCount }} <span class="text-xs font-semibold text-slate-400">Pupuk</span> • {{ $semprotCount }} <span class="text-xs font-semibold text-slate-400">Semprot</span>
            </p>
            <span class="text-[11px] text-agri-600 font-bold mt-2 block">Tindakan Lapangan</span>
        </div>
    </div>

    <!-- Grafik Tren Rekap Aktivitas Periode Ini -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-agri-600"></i>
                    Grafik Tren Pengiriman Laporan Lapangan ({{ $periodeText }})
                </h4>
                <p class="text-xs text-slate-500">Jumlah laporan harian yang disubmit pekerja per waktu</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 rounded-xl bg-agri-50 text-agri-800 border border-agri-200">
                Chart Analytics
            </span>
        </div>
        <div class="h-56 w-full">
            <canvas id="rekapTrendChart"></canvas>
        </div>
    </div>

    <!-- TAMPILAN 1: TABEL LENGKAP (Default jika data banyak) -->
    @if($viewMode === 'table')
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="font-extrabold text-sm text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-table text-agri-600"></i>
                    Tabel Rekapitulasi Laporan Lapangan
                </h4>
                <span class="text-xs text-slate-400 font-medium">Format tabel ringkas & terstruktur</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider font-extrabold text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="p-4">Tanggal & Waktu</th>
                            <th class="p-4">Pekerja</th>
                            <th class="p-4 text-center">Kondisi Tanaman</th>
                            <th class="p-4 text-center">Gulma</th>
                            <th class="p-4 text-center">Hama & Penyakit</th>
                            <th class="p-4 text-center">Ajir & Perempelan</th>
                            <th class="p-4 text-center">Pemupukan / Semprot</th>
                            <th class="p-4 text-center">Foto Sebelum & Sesudah</th>
                            <th class="p-4">Kendala Lapangan</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($laporanList as $lap)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-4 font-bold text-slate-800 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($lap->tanggal)->translatedFormat('d M Y') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">{{ $lap->created_at ? $lap->created_at->format('H:i') : '' }} WIB</span>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="font-extrabold text-slate-800">{{ $lap->user->name ?? 'Pekerja' }}</div>
                                    <span class="text-[10px] text-slate-400">@ {{ $lap->user->username ?? '-' }}</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $lap->kondisi_tanaman === 'Baik' ? 'bg-emerald-100 text-emerald-800' : ($lap->kondisi_tanaman === 'Cukup' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $lap->kondisi_tanaman }}
                                    </span>
                                </td>
                                <td class="p-4 text-center font-semibold text-slate-700">
                                    {{ $lap->gulma }}
                                </td>
                                <td class="p-4 text-center">
                                    <div class="space-y-0.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $lap->hama === 'Ada' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600' }}">
                                            Hama: {{ $lap->hama }}
                                        </span>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $lap->penyakit === 'Ada' ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-600' }}">
                                            Penyakit: {{ $lap->penyakit }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center text-slate-700">
                                    <div>Ajir: <b>{{ $lap->ajir }}</b></div>
                                    <div class="text-[11px] text-slate-500">Perempelan: <b>{{ $lap->perempelan }}</b></div>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="space-y-0.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $lap->pemupukan === 'Dilakukan' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                            Pupuk: {{ $lap->pemupukan }}
                                        </span>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $lap->penyemprotan === 'Dilakukan' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500' }}">
                                            Semprot: {{ $lap->penyemprotan }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        @if($lap->foto_sebelum_url)
                                            <div onclick="showPhotoModal('{{ $lap->foto_sebelum_url }}', 'Foto Sebelum - {{ $lap->user->name ?? '' }} ({{ $lap->tanggal }})')"
                                                class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 cursor-pointer hover:scale-105 transition shadow-sm bg-slate-100" title="Foto Sebelum">
                                                <img src="{{ $lap->foto_sebelum_url }}" alt="Sebelum" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        @if($lap->foto_sesudah_url)
                                            <div onclick="showPhotoModal('{{ $lap->foto_sesudah_url }}', 'Foto Sesudah - {{ $lap->user->name ?? '' }} ({{ $lap->tanggal }})')"
                                                class="w-9 h-9 rounded-lg overflow-hidden border border-slate-200 cursor-pointer hover:scale-105 transition shadow-sm bg-slate-100" title="Foto Sesudah">
                                                <img src="{{ $lap->foto_sesudah_url }}" alt="Sesudah" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        @if(!$lap->foto_sebelum_url && !$lap->foto_sesudah_url)
                                            <span class="text-slate-400 italic text-[11px]">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 max-w-xs text-slate-600 truncate">
                                    {{ $lap->kendala ?: 'Tidak ada kendala' }}
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <a href="{{ route('admin.detail_pekerja', ['id' => $lap->user_id, 'date' => $lap->tanggal]) }}" 
                                        class="px-3 py-1.5 rounded-xl bg-agri-50 hover:bg-agri-100 text-agri-800 font-bold transition inline-flex items-center gap-1 shadow-sm">
                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-12 text-slate-400">
                                    <i class="fa-solid fa-file-excel text-3xl mb-2 text-slate-300 block"></i>
                                    Tidak ada data laporan harian untuk periode {{ $periodeText }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($laporanList->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $laporanList->links() }}
                </div>
            @endif
        </div>

    <!-- TAMPILAN 2: CARDS GRID (Sesuai Mockup 7 & 11) -->
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($laporanList as $lap)
                <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="font-extrabold text-base text-slate-800">{{ $lap->user->name ?? 'Pekerja' }}</h4>
                            <span class="text-xs text-slate-500 font-medium">{{ \Carbon\Carbon::parse($lap->tanggal)->translatedFormat('l, d F Y') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $lap->kondisi_tanaman === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $lap->kondisi_tanaman ?? 'Baik' }}
                            </span>
                            <a href="{{ route('admin.detail_pekerja', ['id' => $lap->user_id, 'date' => $lap->tanggal]) }}" 
                                class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold" title="Lihat Rekap Lengkap">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Status Param Checklist Sesuai Mockup 7 -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🌱 Kondisi:</span>
                            <strong class="text-slate-800">{{ $lap->kondisi_tanaman }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🌿 Gulma:</span>
                            <strong class="text-slate-800">{{ $lap->gulma }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🐛 Hama:</span>
                            <strong class="{{ $lap->hama === 'Ada' ? 'text-rose-600' : 'text-slate-800' }}">{{ $lap->hama }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🦠 Penyakit:</span>
                            <strong class="{{ $lap->penyakit === 'Ada' ? 'text-rose-600' : 'text-slate-800' }}">{{ $lap->penyakit }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🪢 Ajir:</span>
                            <strong class="text-slate-800">{{ $lap->ajir }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">✂️ Perempelan:</span>
                            <strong class="text-slate-800">{{ $lap->perempelan }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">🧪 Pemupukan:</span>
                            <strong class="text-slate-800">{{ $lap->pemupukan }}</strong>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 flex justify-between">
                            <span class="text-slate-500">💨 Penyemprotan:</span>
                            <strong class="text-slate-800">{{ $lap->penyemprotan }}</strong>
                        </div>
                    </div>

                    <!-- Foto Sebelum dan Sesudah Real dari Google Drive -->
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1.5">Foto Sebelum:</span>
                            @if($lap->foto_sebelum_url)
                                <div onclick="showPhotoModal('{{ $lap->foto_sebelum_url }}', 'Foto Sebelum - {{ $lap->user->name ?? '' }} ({{ $lap->tanggal }})')"
                                    class="rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 border border-slate-200 cursor-pointer shadow-sm hover:scale-105 transition">
                                    <img src="{{ $lap->foto_sebelum_url }}" alt="Sebelum" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="rounded-2xl aspect-[4/3] bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-xs italic">
                                    Tidak ada foto
                                </div>
                            @endif
                        </div>

                        <div>
                            <span class="text-[11px] font-bold text-slate-500 block mb-1.5">Foto Sesudah:</span>
                            @if($lap->foto_sesudah_url)
                                <div onclick="showPhotoModal('{{ $lap->foto_sesudah_url }}', 'Foto Sesudah - {{ $lap->user->name ?? '' }} ({{ $lap->tanggal }})')"
                                    class="rounded-2xl overflow-hidden aspect-[4/3] bg-slate-100 border border-slate-200 cursor-pointer shadow-sm hover:scale-105 transition">
                                    <img src="{{ $lap->foto_sesudah_url }}" alt="Sesudah" class="w-full h-full object-cover">
                                </div>
                            @else
                                <div class="rounded-2xl aspect-[4/3] bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center text-slate-400 text-xs italic">
                                    Tidak ada foto
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 text-center py-16 bg-white rounded-3xl border border-slate-200 text-slate-400 text-xs">
                    <i class="fa-solid fa-file-lines text-3xl mb-2 text-slate-300 block"></i>
                    Belum ada laporan harian pada periode ini.
                </div>
            @endforelse
        </div>

        @if($laporanList->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-slate-200">
                {{ $laporanList->links() }}
            </div>
        @endif
    @endif

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

@push('scripts')
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

    // Chart.js Setup for Rekap Trends
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('rekapTrendChart').getContext('2d');
        const labels = @json($chartLabels);
        const dataValues = @json($chartReportsCount);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Laporan Terkirim',
                    data: dataValues,
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.12)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#166534',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 },
                        grid: { color: '#f1f5f9' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
