<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — BrewPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] min-h-screen flex items-center justify-center p-4 antialiased selection:bg-amber-500 selection:text-black">
    <div class="w-full max-w-md">
        <!-- Main Card -->
        <div class="surface-card p-6 sm:p-8 space-y-6 shadow-2xl">
            <!-- Brand Logo & Header -->
            <div class="text-center space-y-2">
                <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-center mx-auto text-amber-500">
                    <i data-lucide="coffee" class="w-6 h-6"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center justify-center gap-2">
                    BrewPOS <span class="text-[10px] uppercase font-mono tracking-wider px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Point of Sale</span>
                </h1>
                <p class="text-xs text-stone-400">Sistem Operasional Kasir & Kedai Kopi</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="p-3.5 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-300 text-xs space-y-1">
                    <div class="flex items-center gap-2 font-bold text-rose-400">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                        <span>Autentikasi Gagal</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p class="pl-6 text-[11px] text-rose-300/90">• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1.5">
                    <label for="email" class="form-label">
                        Email Akun
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </span>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}"
                            required 
                            autofocus
                            placeholder="nama@brewpos.test"
                            class="form-input pl-10"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="form-label">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="••••••••"
                            class="form-input pl-10"
                        >
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            id="remember" 
                            name="remember"
                            class="w-4 h-4 rounded bg-stone-900 border-stone-700 text-amber-600 focus:ring-amber-500/40"
                        >
                        <span class="text-xs text-stone-400">Ingat sesi masuk</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="btn btn-primary w-full text-xs py-2.5 flex items-center justify-center gap-2 font-bold uppercase tracking-wider"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Selector -->
            <div class="pt-4 border-t border-stone-800 space-y-2.5">
                <p class="text-[10px] font-bold text-stone-400 uppercase tracking-wider text-center">Akun Demo (Klik untuk Isi Cepat):</p>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        onclick="fillCreds('admin@brewpos.test')"
                        class="p-2.5 bg-stone-900/40 hover:bg-stone-800/60 border border-stone-800 hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-stone-200 group-hover:text-amber-400">Admin</span>
                        <span class="block text-[9px] text-stone-400 font-mono">Manajemen</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="fillCreds('cashier@brewpos.test')"
                        class="p-2.5 bg-stone-900/40 hover:bg-stone-800/60 border border-stone-800 hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-stone-200 group-hover:text-amber-400">Kasir</span>
                        <span class="block text-[9px] text-stone-400 font-mono">Layar POS</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="fillCreds('barista@brewpos.test')"
                        class="p-2.5 bg-stone-900/40 hover:bg-stone-800/60 border border-stone-800 hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-stone-200 group-hover:text-amber-400">Barista</span>
                        <span class="block text-[9px] text-stone-400 font-mono">Layar KDS</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

        function fillCreds(email) {
            const emailInput = document.getElementById('email');
            const passInput = document.getElementById('password');
            if (emailInput && passInput) {
                emailInput.value = email;
                passInput.value = 'password';
            }
        }
    </script>
</body>
</html>
