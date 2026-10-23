<div x-data="{ open: @entangle('isOpen') }" x-show="open" x-effect="if (open) { $nextTick(() => { if (window.lucide) lucide.createIcons(); }); }" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6" x-cloak>
    <!-- Backdrop Overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="$set('isOpen', false)"></div>

    <!-- Modal Container -->
    <div class="surface-card border border-stone-800 rounded-2xl shadow-2xl z-50 w-full max-w-lg flex flex-col overflow-hidden relative text-stone-200" style="max-height: calc(100vh - 40px); max-height: calc(100dvh - 40px);">
        
        <!-- Modal Header (Fixed at top) -->
        <div class="flex justify-between items-center px-5 py-3.5 border-b border-stone-800 shrink-0 bg-[#181716] z-10">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white tracking-wide">Selesaikan Pembayaran</h3>
                    <p class="text-[11px] text-stone-400">Pilih metode bayar dan masukkan nominal uang</p>
                </div>
            </div>
            <button wire:click="$set('isOpen', false)" class="text-stone-500 hover:text-stone-300 p-1.5 rounded-lg hover:bg-stone-800/50 transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Scrollable Modal Body -->
        <div class="p-5 space-y-4 flex-1" style="overflow-y: auto; -webkit-overflow-scrolling: touch;">
            <!-- Total Bill Display Card -->
            <div class="surface-card-inset p-3.5 rounded-xl flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-stone-400 uppercase tracking-wider block">Total Tagihan</span>
                    <span class="text-[11px] text-stone-500 font-mono">{{ count($cart) }} Item di Keranjang</span>
                </div>
                <div class="text-right">
                    <span class="text-xl sm:text-2xl font-mono font-bold text-stone-100 tabular-nums">
                        Rp {{ number_format($total, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <!-- Form Controls -->
            <div class="space-y-3.5">
                <!-- Global Cart / Stock Error Feedback Container -->
                @error('cart')
                    <div class="p-3 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl text-xs font-semibold flex items-start gap-2.5">
                        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5 text-rose-400"></i>
                        <div class="flex-1">
                            <p class="font-bold">Gagal Memproses Transaksi</p>
                            <p class="text-[11px] text-rose-300/90 mt-0.5 leading-relaxed">{{ $message }}</p>
                        </div>
                    </div>
                @enderror

                <!-- Payment Method Segmented Tabs -->
                <div>
                    <label class="form-label mb-1.5">Metode Pembayaran</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button"
                            wire:click="selectPaymentMethod('cash')"
                            class="py-2 px-3 text-xs font-semibold rounded-lg border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'cash' ? 'bg-amber-600 border-amber-600 text-stone-950 font-bold shadow-md shadow-amber-600/20' : 'bg-stone-900/40 border-stone-800 text-stone-400 hover:text-stone-200' }}"
                        >
                            <i data-lucide="banknote" class="w-4 h-4"></i>
                            <span>Tunai</span>
                        </button>
                        <button 
                            type="button"
                            wire:click="selectPaymentMethod('qris')"
                            class="py-2 px-3 text-xs font-semibold rounded-lg border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'qris' ? 'bg-amber-600 border-amber-600 text-stone-950 font-bold shadow-md shadow-amber-600/20' : 'bg-stone-900/40 border-stone-800 text-stone-400 hover:text-stone-200' }}"
                        >
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                            <span>QRIS</span>
                        </button>
                        <button 
                            type="button"
                            wire:click="selectPaymentMethod('card')"
                            class="py-2 px-3 text-xs font-semibold rounded-lg border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'card' ? 'bg-amber-600 border-amber-600 text-stone-950 font-bold shadow-md shadow-amber-600/20' : 'bg-stone-900/40 border-stone-800 text-stone-400 hover:text-stone-200' }}"
                        >
                            <i data-lucide="credit-card" class="w-4 h-4"></i>
                            <span>Kartu</span>
                        </button>
                    </div>
                </div>

                <!-- Cash Input Section -->
                @if($paymentMethod === 'cash')
                    <div class="space-y-3 pt-1 border-t border-stone-800">
                        <!-- Quick Nominal Preset Pills (Accumulative) -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] text-stone-400 font-medium">Preset Uang Cepat (Akumulasi)</span>
                                @if($cashPaid)
                                    <button 
                                        type="button" 
                                        wire:click="clearCash" 
                                        class="text-[11px] text-rose-400 hover:text-rose-300 font-medium flex items-center gap-1 transition-colors"
                                        title="Reset nominal uang diterima"
                                    >
                                        <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                                        <span>Reset</span>
                                    </button>
                                @endif
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <button 
                                    type="button"
                                    wire:click="setExactCash"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-amber-400 hover:text-amber-300 border-amber-500/30 hover:border-amber-500/60 transition-all flex items-center justify-center text-center active:scale-95"
                                    title="Set nominal pas sesuai tagihan"
                                >
                                    Uang Pas
                                </button>
                                <button 
                                    type="button"
                                    wire:click="addCashAmount(10000)"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-stone-200 hover:text-white hover:border-stone-600 transition-all flex items-center justify-center text-center active:scale-95"
                                >
                                    +10.000
                                </button>
                                <button 
                                    type="button"
                                    wire:click="addCashAmount(20000)"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-stone-200 hover:text-white hover:border-stone-600 transition-all flex items-center justify-center text-center active:scale-95"
                                >
                                    +20.000
                                </button>
                                <button 
                                    type="button"
                                    wire:click="addCashAmount(50000)"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-stone-200 hover:text-white hover:border-stone-600 transition-all flex items-center justify-center text-center active:scale-95"
                                >
                                    +50.000
                                </button>
                                <button 
                                    type="button"
                                    wire:click="addCashAmount(100000)"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-stone-200 hover:text-white hover:border-stone-600 transition-all flex items-center justify-center text-center active:scale-95"
                                >
                                    +100.000
                                </button>
                                <button 
                                    type="button"
                                    wire:click="addCashAmount(200000)"
                                    class="btn btn-secondary py-2.5 px-2 text-xs font-mono font-bold text-stone-200 hover:text-white hover:border-stone-600 transition-all flex items-center justify-center text-center active:scale-95"
                                >
                                    +200.000
                                </button>
                            </div>
                        </div>

                        <!-- Cash Input -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="form-label mb-0">Uang Diterima (Rp)</label>
                                @if($cashPaid)
                                    <span class="text-xs font-mono text-amber-400/90 font-medium">
                                        Rp {{ number_format((float)$cashPaid, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    wire:model.live="cashPaid" 
                                    placeholder="Masukkan nominal tunai..." 
                                    class="form-input text-base font-mono font-bold {{ $cashPaid ? 'pr-9' : '' }}"
                                >
                                @if($cashPaid)
                                    <button 
                                        type="button" 
                                        wire:click="clearCash"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-rose-400 transition-colors p-1"
                                        title="Hapus nominal"
                                    >
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                            @error('cashPaid')
                                <p class="text-xs text-rose-400 font-medium mt-1.5 flex items-center gap-1.5">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        <!-- Change / Deficit Calculation Result Box -->
                        <div>
                            <label class="form-label mb-1.5">Kalkulasi Pembayaran</label>
                            @php
                                $paidVal = (float)($cashPaid ?? 0);
                                $isShortage = $paidVal < $total;
                                $deficit = $total - $paidVal;
                            @endphp
                            <div class="p-3 rounded-xl border flex items-center justify-between font-mono font-bold transition-all {{ $isShortage ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' }}">
                                <span class="text-xs uppercase font-sans tracking-wider text-stone-400">
                                    {{ $isShortage ? 'Kekurangan Uang' : 'Kembalian Pelanggan' }}
                                </span>
                                <span class="text-lg tabular-nums">
                                    Rp {{ number_format($isShortage ? $deficit : $change, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- QRIS Payment Section -->
                @if($paymentMethod === 'qris')
                    <div 
                        class="space-y-2.5 pt-1 border-t border-stone-800" 
                        x-init="$nextTick(() => { if (window.lucide) lucide.createIcons(); })"
                        @if($qrisStatus !== 'paid') wire:poll.3s="checkQrisStatus" @endif
                    >
                        <div class="bg-stone-900/60 border border-stone-800 rounded-xl p-3 flex flex-col items-center text-center relative overflow-hidden">
                            <!-- QRIS Header Badge -->
                            <div class="w-full flex items-center justify-between pb-1.5 mb-1.5 border-b border-stone-800/80">
                                <div class="flex items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black tracking-wider bg-rose-600 text-white uppercase">QRIS</span>
                                    <span class="text-[10px] font-semibold text-stone-300">Standar Pembayaran Nasional</span>
                                </div>
                                <span class="text-[10px] font-mono text-stone-400">Merchant: BrewPOS Coffee</span>
                            </div>

                            <!-- QR Code Container (Crisp, comfortably sized) -->
                            @if($qrisImage)
                                <div class="bg-white p-2 rounded-xl shadow-lg my-1 transition-all border-2 {{ $qrisStatus === 'paid' ? 'border-emerald-500 ring-4 ring-emerald-500/20' : 'border-stone-200' }}">
                                    <img src="{{ $qrisImage }}" alt="QRIS Code" style="width: 160px; height: 160px;" class="block mx-auto select-none" />
                                </div>
                            @else
                                <div style="width: 176px; height: 176px;" class="my-1 flex flex-col items-center justify-center bg-stone-950/60 rounded-xl border border-stone-800 text-stone-500 gap-2">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin text-amber-500"></i>
                                    <span class="text-xs">Menyiapkan QRIS...</span>
                                </div>
                            @endif

                            <!-- Nominal & Reference Info -->
                            <div class="mt-1 text-center w-full space-y-0.5">
                                <div class="text-lg font-mono font-bold text-white tabular-nums">
                                    Rp {{ number_format($total, 0, ',', '.') }}
                                </div>
                                @if($qrisReference)
                                    <div class="text-[10px] font-mono text-stone-400 flex items-center justify-center gap-1.5">
                                        <span>Ref:</span>
                                        <span class="text-amber-400 font-semibold bg-stone-950/80 px-2 py-0.5 rounded border border-stone-800 select-all">{{ $qrisReference }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Status Badge & Helper -->
                            <div class="mt-2 w-full">
                                @if($qrisStatus === 'paid')
                                    <div class="p-2 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-lg text-xs font-semibold flex items-center justify-center gap-2">
                                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-400"></i>
                                        <span>Pembayaran Berhasil Diverifikasi!</span>
                                    </div>
                                @else
                                    <div class="p-2 bg-amber-500/10 border border-amber-500/20 text-amber-300 rounded-lg text-xs flex items-center justify-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span>
                                        <span>Menunggu pelanggan memindai QRIS...</span>
                                    </div>
                                @endif

                                @if($qrisMessage)
                                    <p class="text-[10px] mt-1 text-stone-400">{{ $qrisMessage }}</p>
                                @endif

                                @error('qris')
                                    <p class="text-xs text-rose-400 font-medium mt-1.5 flex items-center justify-center gap-1.5">
                                        <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                        <span>{{ $message }}</span>
                                    </p>
                                @enderror
                            </div>

                            <!-- Action controls for cashier -->
                            <div class="mt-2.5 flex items-center gap-2 w-full">
                                <button 
                                    type="button"
                                    wire:click="checkQrisStatus(true)" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 btn btn-secondary py-1.5 px-2.5 text-[11px] font-semibold flex items-center justify-center gap-1.5"
                                >
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400" wire:loading.class="animate-spin" wire:target="checkQrisStatus"></i>
                                    <span>Cek Status</span>
                                </button>
                                <button 
                                    type="button"
                                    wire:click="simulateQrisPayment"
                                    wire:loading.attr="disabled"
                                    class="btn btn-secondary py-1.5 px-2.5 text-[11px] font-semibold text-stone-400 hover:text-stone-200"
                                    title="Simulasi pelunasan pembayaran QRIS untuk testing"
                                >
                                    <span>Simulasi Bayar</span>
                                </button>
                                <button 
                                    type="button"
                                    wire:click="generateQris" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-secondary py-1.5 px-2.5 text-[11px] font-semibold text-stone-400 hover:text-stone-200"
                                    title="Buat QRIS Baru"
                                >
                                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Card Payment Section -->
                @if($paymentMethod === 'card')
                    <div class="space-y-3 pt-1 border-t border-stone-800" x-init="$nextTick(() => { if (window.lucide) lucide.createIcons(); })">
                        <div class="p-4 bg-stone-900/60 border border-stone-800 rounded-xl text-center space-y-2">
                            <div class="w-10 h-10 rounded-full bg-stone-800 flex items-center justify-center mx-auto text-amber-500">
                                <i data-lucide="credit-card" class="w-5 h-5"></i>
                            </div>
                            <h4 class="text-xs font-bold text-stone-200">Terminal EDC / Kartu</h4>
                            <p class="text-[11px] text-stone-400">Gesek atau tap kartu pelanggan pada terminal EDC, lalu klik tombol Selesaikan Pembayaran di bawah.</p>
                            <div class="text-base font-mono font-bold text-white pt-1">
                                Total: Rp {{ number_format($total, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @php
            $paidVal = (float)($cashPaid ?? 0);
            $cannotSubmitCash = ($paymentMethod === 'cash' && $paidVal < $total);
            $cannotSubmitQris = ($paymentMethod === 'qris' && $qrisStatus !== 'paid');
            $cannotSubmit = $cannotSubmitCash || $cannotSubmitQris || $isProcessing;
        @endphp

        <!-- Action Footer (Fixed at bottom) -->
        <div class="px-5 py-3 border-t border-stone-800 flex gap-2.5 justify-end shrink-0 bg-[#181716] z-10">
            <button 
                type="button"
                wire:click="$set('isOpen', false)" 
                wire:loading.attr="disabled"
                wire:target="createOrder"
                @if($isProcessing) disabled @endif
                class="btn btn-secondary px-4 py-2 text-xs"
            >
                Batal
            </button>
            <button 
                type="button"
                wire:click.prevent="createOrder" 
                wire:loading.attr="disabled"
                wire:target="createOrder"
                @if($cannotSubmit) disabled @endif
                class="btn {{ ($paymentMethod === 'qris' && $qrisStatus === 'paid') ? 'bg-emerald-600 hover:bg-emerald-500 border-emerald-500 text-white shadow-lg shadow-emerald-950/40' : 'btn-primary' }} px-5 py-2 text-xs font-bold uppercase tracking-wider flex items-center gap-2 transition-all {{ $cannotSubmit ? 'opacity-40 cursor-not-allowed pointer-events-none' : '' }}"
            >
                <span wire:loading.remove wire:target="createOrder" class="flex items-center gap-1.5">
                    @if($paymentMethod === 'qris')
                        @if($qrisStatus === 'paid')
                            <i data-lucide="check-check" class="w-4 h-4 text-white"></i>
                            <span>Selesaikan Pembayaran</span>
                        @else
                            <i data-lucide="clock" class="w-4 h-4 text-stone-400"></i>
                            <span>Menunggu Pembayaran QRIS</span>
                        @endif
                    @else
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span>Proses Pembayaran</span>
                    @endif
                </span>
                <span wire:loading wire:target="createOrder" class="flex items-center gap-1.5">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    <span>Memproses...</span>
                </span>
            </button>
        </div>
    </div>
</div>
