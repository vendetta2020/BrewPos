<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutup Sesi Shift Kasir — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-xl w-full bg-[#181716] rounded-2xl shadow-2xl overflow-hidden border border-[#2D2A26] my-6">
        <!-- Header -->
        <div class="bg-[#141312] p-6 text-center border-b border-[#2D2A26]">
            <div class="w-14 h-14 bg-rose-500/10 border border-rose-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3 text-rose-400">
                <i data-lucide="lock" class="w-7 h-7"></i>
            </div>
            <h1 class="text-xl font-extrabold text-white tracking-wide">Rekap & Tutup Sesi Shift Kasir</h1>
            <p class="text-xs font-mono text-[#A8A29E] mt-1">Sesi #{{ $session->id }} • Opened: {{ $session->opened_at->format('d M Y H:i') }}</p>
        </div>

        <!-- Sales Summary Grid -->
        <div class="p-6 bg-[#141312]/60 border-b border-[#2D2A26] space-y-4">
            <h2 class="text-xs font-bold text-[#A8A29E] uppercase tracking-wider">Ringkasan Penjualan Sesi Ini</h2>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-[#181716] p-3.5 rounded-xl border border-[#2D2A26]">
                    <p class="text-[11px] text-[#78716C] font-medium">Modal Kas Awal</p>
                    <p class="text-base font-mono font-bold text-[#F3F0EA] mt-0.5">Rp {{ number_format($session->opening_balance, 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#181716] p-3.5 rounded-xl border border-[#2D2A26]">
                    <p class="text-[11px] text-[#78716C] font-medium">Total Transaksi</p>
                    <p class="text-base font-mono font-bold text-amber-400 mt-0.5">{{ $orderCount }} Pesanan</p>
                </div>
                <div class="bg-[#181716] p-3.5 rounded-xl border border-[#2D2A26]">
                    <p class="text-[11px] text-[#78716C] font-medium">Penjualan Tunai</p>
                    <p class="text-base font-mono font-bold text-emerald-400 mt-0.5">Rp {{ number_format($cashSales, 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#181716] p-3.5 rounded-xl border border-[#2D2A26]">
                    <p class="text-[11px] text-[#78716C] font-medium">Non-Tunai (QRIS/Kartu)</p>
                    <p class="text-base font-mono font-bold text-sky-400 mt-0.5">Rp {{ number_format($qrisSales + $cardSales, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Expected Cash Box -->
            <div class="bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl flex justify-between items-center">
                <div>
                    <p class="text-xs font-bold text-amber-400">Ekspektasi Uang Tunai di Laci</p>
                    <p class="text-[10px] text-[#A8A29E] mt-0.5">(Modal Awal + Penjualan Tunai)</p>
                </div>
                <p class="text-xl font-mono font-extrabold text-amber-300" id="expected-cash-display">
                    Rp {{ number_format($expectedCash, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Form Body -->
        <form method="POST" action="{{ route('cashier.session.update') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label for="closing_balance" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-2">
                    Uang Kas Fisik Akhir di Laci (Rp) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-amber-500 font-mono font-bold text-sm">Rp</span>
                    <input 
                        type="number" 
                        id="closing_balance" 
                        name="closing_balance" 
                        value="{{ old('closing_balance', $expectedCash) }}"
                        min="0"
                        step="1000"
                        required
                        class="w-full pl-10 pr-4 py-3 bg-[#141312] border border-[#2D2A26] focus:border-rose-500 focus:ring-1 focus:ring-rose-500 text-white text-lg font-mono font-bold rounded-xl transition-all"
                    >
                </div>
                @error('closing_balance')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Live Difference Status Alert -->
            <div id="diff-container" class="p-3.5 rounded-xl border text-xs font-mono font-bold flex justify-between items-center bg-[#141312] border-[#2D2A26] text-stone-300">
                <span class="font-sans font-semibold text-[#A8A29E]">Status Selisih Kas:</span>
                <span id="diff-amount" class="text-sm">Rp 0</span>
            </div>

            <div>
                <label for="notes" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-2">Catatan Penutupan Shift (Opsional)</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-rose-500 focus:ring-1 focus:ring-rose-500 text-stone-200 text-xs rounded-xl transition-all placeholder-[#57534E]"
                    placeholder="Contoh: Selisih Rp 5.000 karena salah kembalian"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <a href="{{ route('pos') }}" class="w-1/3 py-3 text-center bg-[#242220] hover:bg-[#2D2A26] text-stone-300 text-xs font-semibold rounded-xl border border-[#383430] transition-colors flex items-center justify-center gap-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Batal</span>
                </a>
                <button type="submit" class="flex-1 py-3 bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-lg hover:shadow-rose-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                    <i data-lucide="lock" class="w-4 h-4"></i>
                    <span>Tutup Sesi & Logout</span>
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });

        const expectedCash = {{ $expectedCash }};
        const closingInput = document.getElementById('closing_balance');
        const diffContainer = document.getElementById('diff-container');
        const diffAmount = document.getElementById('diff-amount');

        function updateDiff() {
            const closingVal = parseFloat(closingInput.value) || 0;
            const diff = closingVal - expectedCash;

            diffAmount.textContent = 'Rp ' + diff.toLocaleString('id-ID');

            if (diff === 0) {
                diffContainer.className = "p-3.5 rounded-xl border text-xs font-mono font-bold flex justify-between items-center bg-emerald-500/10 border-emerald-500/30 text-emerald-400";
            } else if (diff < 0) {
                diffContainer.className = "p-3.5 rounded-xl border text-xs font-mono font-bold flex justify-between items-center bg-rose-500/10 border-rose-500/30 text-rose-400";
            } else {
                diffContainer.className = "p-3.5 rounded-xl border text-xs font-mono font-bold flex justify-between items-center bg-sky-500/10 border-sky-500/30 text-sky-400";
            }
        }

        closingInput.addEventListener('input', updateDiff);
        updateDiff();
    </script>
</body>
</html>

