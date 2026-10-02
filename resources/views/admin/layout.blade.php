<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - AGROCOM</title>
    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0d381e">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        agri: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            dark: '#0d381e',
                        },
                        chili: {
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js for Visual Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet CSS & JS for Interactive Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Header -->
    <div class="md:hidden bg-agri-dark text-white p-4 flex items-center justify-between shadow-md sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <img src="{{ asset('apple-touch-icon.png') }}" alt="AGROCOM Logo" class="w-9 h-9 rounded-xl object-cover shadow border border-white/10">
            <div>
                <span class="font-black text-lg tracking-wider">AGRO<span class="text-emerald-400">COM</span></span>
                <span class="text-[10px] block text-agri-200">Panel Admin Kebun</span>
            </div>
        </div>
        <button id="mobileMenuBtn" class="p-2 text-white hover:text-emerald-300">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Sidebar Backdrop for mobile -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"></div>

    <!-- Sidebar -->
    <aside id="mainSidebar" class="w-64 bg-agri-dark text-white flex-shrink-0 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 md:static transition duration-200 flex flex-col justify-between shadow-2xl">
        <div class="overflow-y-auto flex-1">
            <!-- Logo Brand -->
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('apple-touch-icon.png') }}" alt="AGROCOM Logo" class="w-10 h-10 rounded-2xl object-cover shadow-lg border border-white/10">
                    <div>
                        <h1 class="text-xl font-extrabold tracking-wide text-white">AGRO<span class="text-emerald-400">COM</span></h1>
                        <p class="text-[11px] text-agri-200">Manajemen & Monitoring</p>
                    </div>
                </div>
                <button id="closeSidebarBtn" class="md:hidden text-white/70 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="p-3 space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard') ? 'bg-agri-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard Utama</span>
                </a>
                
                <a href="{{ route('admin.monitoring_peta') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.monitoring_peta') ? 'bg-agri-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-map-location-dot w-5 text-center"></i>
                    <span>Monitoring Kebun (Peta)</span>
                </a>
                
                <a href="{{ route('admin.absensi') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.absensi') ? 'bg-agri-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-clipboard-user w-5 text-center"></i>
                    <span>Rekap Absensi Pekerja</span>
                </a>
                
                <a href="{{ route('admin.laporan_masalah') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.laporan_masalah*') ? 'bg-agri-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-bug w-5 text-center"></i>
                    <span>Laporan Masalah & Hama</span>
                </a>
                
                <a href="{{ route('admin.laporan_harian') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.laporan_harian*') ? 'bg-agri-600 text-white shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-file-waveform w-5 text-center"></i>
                    <span>Rekap Laporan Harian</span>
                </a>

                @if(Auth::user()->isAdmin())
                <!-- SECTION DATA MASTER (KHUSUS ADMINISTRATOR) -->
                <div class="pt-5 pb-1 px-3 text-[11px] font-extrabold uppercase tracking-wider text-agri-200/60 flex items-center justify-between">
                    <span>Data Master</span>
                    <i class="fa-solid fa-sliders text-[10px]"></i>
                </div>

                <a href="{{ route('admin.master.lahan') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs transition {{ request()->routeIs('admin.master.lahan*') ? 'bg-agri-600 text-white font-bold shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-layer-group w-5 text-center"></i>
                    <span>Lokasi & Blok Lahan</span>
                </a>

                <a href="{{ route('admin.master.pekerja') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs transition {{ request()->routeIs('admin.master.pekerja*') ? 'bg-agri-600 text-white font-bold shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-users w-5 text-center"></i>
                    <span>Data Pekerja</span>
                </a>

                <a href="{{ route('admin.master.tugas_harian') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs transition {{ request()->routeIs('admin.master.tugas_harian*') ? 'bg-agri-600 text-white font-bold shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span>Master Tugas Harian</span>
                </a>

                <a href="{{ route('admin.master.data_dukung') }}" 
                   class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs transition {{ request()->routeIs('admin.master.data_dukung*') ? 'bg-agri-600 text-white font-bold shadow-md' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <i class="fa-solid fa-shield-virus w-5 text-center"></i>
                    <span>Master Data Dukung</span>
                </a>
                @endif
            </nav>
        </div>

        <!-- User / Storage Info & Logout -->
        <div class="p-3 border-t border-white/10 bg-black/30 text-xs space-y-2.5">
            <div class="flex items-center justify-between text-slate-300">
                <div class="flex items-center gap-2">
                    <i class="fa-brands fa-google-drive text-amber-400"></i>
                    <span class="font-medium text-[11px]">Google Drive:</span>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Aktif</span>
            </div>

            <!-- Profile & Logout Bar -->
            <div class="pt-2 border-t border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <div class="w-8 h-8 rounded-full bg-agri-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-bold text-xs text-white truncate flex items-center gap-1.5">
                            <span>{{ Auth::user()->name ?? 'User' }}</span>
                            <span class="text-[9px] px-1.5 py-0.2 rounded font-extrabold uppercase {{ Auth::user()->isAdmin() ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/30' : 'bg-amber-500/30 text-amber-300 border border-amber-400/30' }}">
                                {{ Auth::user()->role }}
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email ?? 'user@agrocom.id' }}</div>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" 
                        class="p-2 text-rose-300 hover:text-rose-100 hover:bg-rose-500/20 rounded-lg transition"
                        onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Topbar -->
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-30 shadow-sm">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-extrabold text-slate-800">@yield('page_title', 'Dashboard')</h2>
                
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-slate-800 flex items-center gap-1.5 justify-end">
                        <span>{{ Auth::user()->name ?? 'Pengguna' }}</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-extrabold uppercase {{ Auth::user()->isAdmin() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-agri-700 text-white flex items-center justify-center font-extrabold shadow-md border-2 border-agri-200">
                    {{ strtoupper(substr(Auth::user()->name ?? 'AP', 0, 2)) }}
                </div>
                
                <form action="{{ route('logout') }}" method="POST" class="hidden sm:block">
                    @csrf
                    <button type="submit" title="Logout dari Panel Admin"
                        class="px-3 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-rose-700 hover:bg-rose-50 border border-slate-200 transition flex items-center gap-1.5"
                        onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?')">
                        <i class="fa-solid fa-power-off text-rose-500"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="mx-6 mt-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg flex-shrink-0"></i>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-3 shadow-sm">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg flex-shrink-0"></i>
                <div class="text-sm font-semibold">{{ session('error') }}</div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mx-6 mt-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center gap-2 font-bold text-sm text-rose-900 mb-1">
                    <i class="fa-solid fa-circle-xmark text-rose-600"></i>
                    <span>Terdapat kesalahan input:</span>
                </div>
                <ul class="text-xs list-disc list-inside space-y-0.5 ml-2">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Page Body -->
        <main class="p-6">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Drawer JS -->
    <script>
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const sidebar = document.getElementById('mainSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const closeBtn = document.getElementById('closeSidebarBtn');

        function toggleMenu() {
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        if (mobileBtn) mobileBtn.addEventListener('click', toggleMenu);
        if (backdrop) backdrop.addEventListener('click', toggleMenu);
        if (closeBtn) closeBtn.addEventListener('click', toggleMenu);
    </script>

    @stack('scripts')
</body>
</html>
