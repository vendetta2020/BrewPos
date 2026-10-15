<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] min-h-screen flex items-center justify-center p-4 antialiased selection:bg-amber-500 selection:text-black">
    <div class="w-full max-w-md">
        <!-- Main Card -->
        <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl shadow-2xl overflow-hidden p-8 space-y-6">
            <!-- Brand Logo & Header -->
            <div class="text-center space-y-2">
                <div class="w-14 h-14 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center mx-auto text-amber-500 shadow-md">
                    <i data-lucide="coffee" class="w-7 h-7"></i>
                </div>
                <h1 class="text-2xl font-extrabold text-white tracking-tight flex items-center justify-center gap-2">
                    BrewPOS <span class="text-[10px] uppercase font-mono tracking-wider px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Point of Sale</span>
                </h1>
                <p class="text-xs text-[#A8A29E]">Sistem Kasir & Manajemen Kedai Kopi Modern</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 rounded-xl text-rose-300 text-xs space-y-1">
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
                    <label for="email" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Email Akun
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#78716C]">
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
                            class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-white text-xs font-medium rounded-xl transition-all placeholder-[#57534E]"
                        >
                    </div>
                </div>

                <!-- Password Input -->
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#78716C]">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </span>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-white text-xs font-medium rounded-xl transition-all placeholder-[#57534E]"
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
                            class="w-4 h-4 rounded bg-[#141312] border-[#2D2A26] text-amber-600 focus:ring-amber-500/40"
                        >
                        <span class="text-xs text-[#A8A29E]">Ingat sesi masuk</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-amber-600/20 transition-all flex items-center justify-center gap-2"
                >
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Quick Demo Credentials Selector -->
            <div class="pt-4 border-t border-[#2D2A26] space-y-2.5">
                <p class="text-[10px] font-bold text-[#78716C] uppercase tracking-wider text-center">Akun Demo (Klik untuk Isi Cepat):</p>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button" 
                        onclick="fillCreds('admin@brewpos.test')"
                        class="p-2 bg-[#141312] hover:bg-[#201D1A] border border-[#2D2A26] hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-amber-400 group-hover:text-amber-300">Admin</span>
                        <span class="block text-[9px] text-[#78716C] font-mono">Manajemen</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="fillCreds('cashier@brewpos.test')"
                        class="p-2 bg-[#141312] hover:bg-[#201D1A] border border-[#2D2A26] hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-emerald-400 group-hover:text-emerald-300">Kasir</span>
                        <span class="block text-[9px] text-[#78716C] font-mono">Layar POS</span>
                    </button>
                    <button 
                        type="button" 
                        onclick="fillCreds('barista@brewpos.test')"
                        class="p-2 bg-[#141312] hover:bg-[#201D1A] border border-[#2D2A26] hover:border-amber-500/40 rounded-xl text-center transition group"
                    >
                        <span class="block text-[11px] font-bold text-purple-400 group-hover:text-purple-300">Barista</span>
                        <span class="block text-[9px] text-[#78716C] font-mono">Layar KDS</span>
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
