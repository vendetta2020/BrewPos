<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutup Sesi Shift Kasir — BrewPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans min-h-screen flex items-center justify-center p-4 antialiased">
    <div class="max-w-xl w-full surface-card shadow-2xl overflow-hidden my-6">
        <!-- Header -->
        <div class="bg-stone-900/60 p-6 text-center border-b border-stone-800">
            <div class="w-12 h-12 bg-rose-500/10 border border-rose-500/20 rounded-xl flex items-center justify-center mx-auto mb-3 text-rose-400">
                <i data-lucide="lock" class="w-6 h-6"></i>
            </div>
            <h1 class="text-lg font-bold text-white tracking-wide">Rekap & Tutup Sesi Shift Kasir</h1>
            <p class="text-xs font-mono text-stone-400 mt-1">Sesi #{{ $session->id }} • Dibuka: {{ $session->opened_at->format('d M Y H:i') }}</p>
        </div>

        <!-- Sales Summary Grid -->
        <div class="p-5 sm:p-6 bg-stone-900/30 border-b border-stone-800 space-y-4">
            <h2 class="text-xs font-semibold text-stone-300 uppercase tracking-wider">Ringkasan Penjualan Sesi Ini</h2>
            <div class="grid grid-cols-2 gap-3">
                <div class="stat-tile">
                    <p class="stat-tile-label">Modal Kas Awal</p>
                    <p class="stat-tile-value text-base sm:text-lg">Rp {{ number_format($session->opening_balance, 0, ',', '.') }}</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">Total Transaksi</p>
                    <p class="stat-tile-value text-base sm:text-lg">{{ $orderCount }} Pesanan</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">Penjualan Tunai</p>
                    <p class="stat-tile-value text-base sm:text-lg text-emerald-400">Rp {{ number_format($cashSales, 0, ',', '.') }}</p>
                </div>
                <div class="stat-tile">
                    <p class="stat-tile-label">Non-Tunai (QRIS/Kartu)</p>
                    <p class="stat-tile-value text-base sm:text-lg text-sky-400">Rp {{ number_format($qrisSales + $cardSales, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Expected Cash Box -->
            <div class="bg-stone-900/60 border border-stone-800 p-4 rounded-xl flex justify-between items-center">
                <div>
                    <p class="text-xs font-semibold text-stone-200">Ekspektasi Uang Tunai di Laci</p>
                    <p class="text-[10px] text-stone-500 mt-0.5">(Modal Awal + Penjualan Tunai)</p>
                </div>
                <p class="text-lg font-mono font-bold text-stone-100" id="expected-cash-display">
                    Rp {{ number_format($expectedCash, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <!-- Form Body -->
        <form method="POST" action="{{ route('cashier.session.update') }}" class="p-6 space-y-5">
            @csrf

            <div>
                <label for="closing_balance" class="form-label mb-1.5">
                    Uang Kas Fisik Akhir di Laci (Rp) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-stone-400 font-mono font-medium text-sm">Rp</span>
                    <input 
                        type="number" 
                        id="closing_balance" 
                        name="closing_balance" 
                        value="{{ old('closing_balance', $expectedCash) }}"
                        min="0"
                        step="1000"
                        required
                        class="form-input pl-10 pr-4 py-2.5 text-base font-mono font-bold"
                    >
                </div>
                @error('closing_balance')
                    <p class="text-rose-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Live Difference Status Alert -->
            <div id="diff-container" class="p-3.5 rounded-xl border text-xs font-mono font-bold flex justify-between items-center bg-stone-900/50 border-stone-800 text-stone-300">
                <span class="font-sans font-medium text-stone-400">Status Selisih Kas:</span>
                <span id="diff-amount" class="text-sm">Rp 0</span>
            </div>

            <div>
                <label for="notes" class="form-label mb-1.5">Catatan Penutupan Shift (Opsional)</label>
                <textarea 
                    id="notes" 
                    name="notes" 
                    rows="2"
                    class="form-input text-xs"
                    placeholder="Contoh: Selisih Rp 5.000 karena salah kembalian"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2 flex gap-3">
                <a href="{{ route('pos') }}" class="btn btn-secondary w-1/3 py-2.5 text-xs inline-flex items-center justify-center gap-1.5">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Batal</span>
                </a>
                <button type="submit" class="btn btn-destructive flex-1 py-2.5 text-xs uppercase tracking-wider font-bold inline-flex items-center justify-center gap-2">
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

