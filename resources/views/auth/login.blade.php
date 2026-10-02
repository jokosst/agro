<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Web Admin - AGROCOM</title>
    
    <!-- Favicon & Icons -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#0d381e">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
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
                            950: '#052e16',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-agri-950 to-slate-900 min-h-screen flex items-center justify-center p-4 relative overflow-hidden font-sans">
    
    <!-- Background subtle mesh/glow -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-agri-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-emerald-500/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="w-full max-w-md relative z-10">
        
        <!-- Header Branding -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-tr from-agri-600 to-emerald-400 p-1 shadow-2xl shadow-agri-600/40 mb-4 transform hover:scale-105 transition duration-300">
                <div class="w-full h-full bg-slate-900 rounded-[22px] flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('apple-touch-icon.png') }}" alt="AGROCOM Logo" class="w-full h-full object-cover">
                </div>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">
                AGRO<span class="text-emerald-400">COM</span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Sistem Manajemen & Monitoring Kebun </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl border border-white/20">
            
            <div class="mb-6">
                <h2 class="text-xl font-bold text-slate-800">Masuk ke Panel Admin</h2>
                <p class="text-xs text-slate-500 mt-1">Silakan masukkan username atau email admin Anda</p>
            </div>

            <!-- Error Alerts -->
            @if(isset($errors) && $errors->any())
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm mt-0.5"></i>
                    <div>
                        <span class="font-bold">Gagal Masuk:</span>
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Username / Email Field -->
                <div>
                    <label for="login" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Username atau Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <input type="text" id="login" name="login" value="{{ old('login', 'admin') }}" required autofocus
                            class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 focus:border-agri-600 focus:ring-4 focus:ring-agri-600/10 text-sm text-slate-800 font-medium transition placeholder:text-slate-400"
                            placeholder="Contoh: admin atau admin@agrocom.id">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-11 pr-11 py-3 rounded-xl border border-slate-200 focus:border-agri-600 focus:ring-4 focus:ring-agri-600/10 text-sm text-slate-800 font-medium transition placeholder:text-slate-400"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-agri-600 focus:ring-agri-600 border-slate-300">
                        <span class="text-xs font-medium text-slate-600">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-agri-700 to-agri-600 hover:from-agri-800 hover:to-agri-700 text-white font-bold text-sm shadow-lg shadow-agri-700/30 transform active:scale-[0.98] transition duration-200 flex items-center justify-center gap-2">
                    <span>Masuk ke Dashboard</span>
                    <i class="fa-solid fa-arrow-right text-xs"></i>
                </button>
            </form>

            <!-- Quick Demo Credentials Box -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-2">Akun Demo Cepat</span>
                <button type="button" onclick="fillAdmin()" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-700 hover:text-emerald-700 text-xs font-semibold transition border border-slate-200">
                    <i class="fa-solid fa-key text-[10px] text-agri-600"></i>
                    <span>Admin: admin / 123456</span>
                </button>
            </div>

        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400 mt-6">
            &copy; 2026 AGROCOM. Seluruh hak cipta dilindungi.
        </p>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', () => {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            toggleIcon.className = isPassword ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
        });

        function fillAdmin() {
            document.getElementById('login').value = 'admin';
            document.getElementById('password').value = '123456';
        }
    </script>
</body>
</html>
