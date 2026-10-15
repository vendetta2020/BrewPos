<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buka Sesi Kasir — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-md w-full bg-[#181716] rounded-2xl shadow-2xl overflow-hidden border border-[#2D2A26]">
        <!-- Header -->
        <div class="bg-[#141312] p-6 text-center border-b border-[#2D2A26]">
            <div class="w-14 h-14 bg-amber-500/10 border border-amber-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-400">
                <i data-lucide="unlock" class="w-7 h-7"></i>
            </div>
            <h1 class="text-xl font-extrabold text-white tracking-wide">Buka Sesi Shift Kasir</h1>
            <p class="text-xs text-[#A8A29E] mt-1 flex items-center justify-center gap-1">
                <i data-lucide="user" class="w-3.5 h-3.5 text-amber-500"></i> Kasir: <span class="font-semibold text-white">{{ auth()->user()->name }}</span>
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
                <label for="opening_balance" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-2">
                    Modal Kas Awal (Rp) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-amber-500 font-mono font-bold text-sm">Rp</span>
                    <input 
                        type="number" 
                        id="opening_balance" 
                        name="opening_balance" 
                        value="{{ old('opening_balance', 0) }}"
                        min="0"
                        step="1000"
                        required
                        class="w-full pl-10 pr-4 py-3 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-white text-lg font-mono font-bold rounded-xl transition-all"
                        placeholder="0"
                    >
                </div>
                @error('opening_balance')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-[11px] text-[#78716C] mt-1.5">Masukkan jumlah uang tunai di laci kasir sebelum transaksi dimulai.</p>
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-2">Catatan Shift (Opsional)</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-stone-200 text-xs rounded-xl transition-all placeholder-[#57534E]"
                    placeholder="Contoh: Shift Pagi, kondisi laci OK"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg hover:shadow-emerald-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    <i data-lucide="play-circle" class="w-4 h-4"></i>
                    <span>Mulai Sesi Shift</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>

