<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Sesi Kasir — BrewPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full surface-card shadow-2xl overflow-hidden">
        <!-- Header -->
        <div class="bg-stone-900/60 p-6 text-center border-b border-stone-800">
            <div class="w-12 h-12 bg-amber-500/10 border border-amber-500/20 rounded-xl flex items-center justify-center mx-auto mb-3 text-amber-500">
                <i data-lucide="unlock" class="w-6 h-6"></i>
            </div>
            <h1 class="text-lg font-bold text-white tracking-wide">Buka Sesi Shift Kasir</h1>
            <p class="text-xs text-stone-400 mt-1 flex items-center justify-center gap-1.5">
                <i data-lucide="user" class="w-3.5 h-3.5 text-stone-400"></i> Kasir: <span class="font-medium text-stone-200">{{ auth()->user()->name }}</span>
            </p>
        </div>

        <!-- Form Body -->
        <form method="POST" action="{{ route('cashier.session.store') }}" class="p-6 space-y-5">
            @csrf

            @if(session('warning'))
                <div class="p-3 bg-amber-500/10 border border-amber-500/30 text-amber-300 rounded-xl text-xs flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400 shrink-0"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <div>
                <label for="opening_balance" class="form-label mb-1.5">
                    Modal Kas Awal (Rp) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 font-mono font-medium text-sm">Rp</span>
                    <input 
                        type="number" 
                        id="opening_balance" 
                        name="opening_balance" 
                        value="{{ old('opening_balance', 0) }}"
                        min="0"
                        step="1000"
                        required
                        class="form-input pl-10 pr-4 py-2.5 text-base font-mono font-bold"
                        placeholder="0"
                    >
                </div>
                @error('opening_balance')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="form-help">Masukkan jumlah uang tunai di laci kasir sebelum transaksi dimulai.</p>
            </div>

            <div>
                <label for="notes" class="form-label mb-1.5">Catatan Shift (Opsional)</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2"
                    class="form-input text-xs"
                    placeholder="Contoh: Shift Pagi, kondisi laci OK"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn btn-primary w-full py-2.5 text-xs uppercase tracking-wider font-bold flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    <span>Mulai Sesi Shift</span>
                </button>
            </div>
        </form>

        <!-- Switch Cashier Account -->
        <div class="px-6 pb-6 pt-1 border-t border-stone-800/60">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="w-full py-2.5 px-3 text-xs text-stone-400 hover:text-stone-200 hover:bg-stone-900/60 rounded-lg transition-colors flex items-center justify-center gap-2 border border-stone-800 hover:border-stone-700 font-medium cursor-pointer"
                >
                    <i data-lucide="log-out" class="w-3.5 h-3.5 text-stone-400"></i>
                    <span>Ganti Akun Kasir</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>

