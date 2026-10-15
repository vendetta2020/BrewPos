<div x-data="{ open: @entangle('isOpen') }" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
    <!-- Backdrop Overlay -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" wire:click="$set('isOpen', false)"></div>

    <!-- Modal Container -->
    <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl shadow-2xl z-50 w-full max-w-lg p-6 overflow-hidden relative text-[#F3F0EA] space-y-5">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b border-[#2D2A26]">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white tracking-wide">Selesaikan Pembayaran</h3>
                    <p class="text-[11px] text-[#A8A29E]">Pilih metode bayar dan masukkan nominal uang</p>
                </div>
            </div>
            <button wire:click="$set('isOpen', false)" class="text-[#78716C] hover:text-white p-1 rounded-lg hover:bg-[#242220] transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Total Bill Display Card -->
        <div class="bg-[#141312] border border-[#2D2A26] rounded-xl p-4 flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold text-[#A8A29E] uppercase tracking-wider block">Total Tagihan</span>
                <span class="text-[11px] text-[#78716C] font-mono">{{ count($cart) }} Item di Keranjang</span>
            </div>
            <div class="text-right">
                <span class="text-2xl font-mono font-extrabold text-amber-400">
                    Rp {{ number_format($total, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <!-- Form Controls -->
        <div class="space-y-4">
            <!-- Global Cart / Stock Error Feedback Container -->
            @error('cart')
                <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl text-xs font-semibold flex items-start gap-2.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5 text-rose-400"></i>
                    <div class="flex-1">
                        <p class="font-bold">Gagal Memproses Transaksi</p>
                        <p class="text-[11px] text-rose-300/90 mt-0.5 leading-relaxed">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            <!-- Payment Method Segmented Tabs -->
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-2">Metode Pembayaran</label>
                <div class="grid grid-cols-3 gap-2">
                    <button 
                        type="button"
                        wire:click="$set('paymentMethod', 'cash')"
                        class="py-2.5 px-3 text-xs font-semibold rounded-xl border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'cash' ? 'bg-amber-600 border-amber-500 text-white shadow-md shadow-amber-600/20' : 'bg-[#242220] border-[#383430] text-[#A8A29E] hover:text-white hover:border-[#443F3A]' }}"
                    >
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                        <span>Tunai</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="$set('paymentMethod', 'qris')"
                        class="py-2.5 px-3 text-xs font-semibold rounded-xl border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'qris' ? 'bg-amber-600 border-amber-500 text-white shadow-md shadow-amber-600/20' : 'bg-[#242220] border-[#383430] text-[#A8A29E] hover:text-white hover:border-[#443F3A]' }}"
                    >
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        <span>QRIS</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="$set('paymentMethod', 'card')"
                        class="py-2.5 px-3 text-xs font-semibold rounded-xl border transition-all flex items-center justify-center gap-2 {{ $paymentMethod === 'card' ? 'bg-amber-600 border-amber-500 text-white shadow-md shadow-amber-600/20' : 'bg-[#242220] border-[#383430] text-[#A8A29E] hover:text-white hover:border-[#443F3A]' }}"
                    >
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Kartu</span>
                    </button>
                </div>
            </div>

            <!-- Cash Input Section -->
            @if($paymentMethod === 'cash')
                <div class="space-y-3 pt-1 border-t border-[#242220]">
                    <!-- Quick Nominal Preset Pills -->
                    <div>
                        <span class="block text-[11px] text-[#A8A29E] font-medium mb-1.5">Preset Uang Cepat</span>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                type="button"
                                wire:click="$set('cashPaid', {{ $total }})"
                                class="px-2.5 py-1 text-xs font-mono font-semibold bg-[#242220] hover:bg-amber-600 hover:text-white text-amber-400 border border-[#383430] rounded-lg transition-colors"
                            >
                                Uang Pas
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('cashPaid', 50000)"
                                class="px-2.5 py-1 text-xs font-mono font-semibold bg-[#242220] hover:bg-amber-600 hover:text-white text-stone-300 border border-[#383430] rounded-lg transition-colors"
                            >
                                50.000
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('cashPaid', 100000)"
                                class="px-2.5 py-1 text-xs font-mono font-semibold bg-[#242220] hover:bg-amber-600 hover:text-white text-stone-300 border border-[#383430] rounded-lg transition-colors"
                            >
                                100.000
                            </button>
                            <button 
                                type="button"
                                wire:click="$set('cashPaid', 200000)"
                                class="px-2.5 py-1 text-xs font-mono font-semibold bg-[#242220] hover:bg-amber-600 hover:text-white text-stone-300 border border-[#383430] rounded-lg transition-colors"
                            >
                                200.000
                            </button>
                        </div>
                    </div>

                    <!-- Cash Input -->
                    <div>
                        <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Uang Diterima (Rp)</label>
                        <div class="relative">
                            <input 
                                type="number" 
                                wire:model.live="cashPaid" 
                                placeholder="Masukkan nominal tunai..." 
                                class="w-full pl-4 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-white font-mono font-bold text-base rounded-xl transition-all placeholder-[#57534E]"
                            >
                        </div>
                        @error('cashPaid')
                            <p class="text-xs text-rose-400 font-semibold mt-1.5 flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Change / Deficit Calculation Result Box -->
                    <div>
                        <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Kalkulasi Pembayaran</label>
                        @php
                            $paidVal = (float)($cashPaid ?? 0);
                            $isShortage = $paidVal < $total;
                            $deficit = $total - $paidVal;
                        @endphp
                        <div class="p-3 rounded-xl border flex items-center justify-between font-mono font-bold transition-all {{ $isShortage ? 'bg-amber-500/10 border-amber-500/30 text-amber-400' : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' }}">
                            <span class="text-xs uppercase font-sans tracking-wider text-[#A8A29E]">
                                {{ $isShortage ? 'Kekurangan Uang' : 'Kembalian Pelanggan' }}
                            </span>
                            <span class="text-lg">
                                Rp {{ number_format($isShortage ? $deficit : $change, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @php
            $paidVal = (float)($cashPaid ?? 0);
            $cannotSubmitCash = ($paymentMethod === 'cash' && $paidVal < $total);
        @endphp

        <!-- Action Footer -->
        <div class="pt-3 border-t border-[#2D2A26] flex gap-3 justify-end">
            <button 
                type="button"
                wire:click="$set('isOpen', false)" 
                wire:loading.attr="disabled"
                wire:target="createOrder"
                @if($isProcessing) disabled @endif
                class="px-4 py-2.5 bg-[#242220] hover:bg-[#2D2A26] disabled:opacity-40 disabled:cursor-not-allowed text-stone-300 text-xs font-semibold rounded-xl border border-[#383430] transition-colors"
            >
                Batal
            </button>
            <button 
                type="button"
                wire:click.prevent="createOrder" 
                wire:loading.attr="disabled"
                wire:target="createOrder"
                @if($cannotSubmitCash || $isProcessing) disabled @endif
                class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-emerald-600 text-white text-xs font-extrabold tracking-wider uppercase rounded-xl shadow-lg hover:shadow-emerald-600/20 active:scale-[0.99] transition-all flex items-center gap-2"
            >
                <span wire:loading.remove wire:target="createOrder" class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Proses Pembayaran</span>
                </span>
                <span wire:loading wire:target="createOrder" class="flex items-center gap-2">
                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    <span>Memproses Transaksi...</span>
                </span>
            </button>
        </div>
    </div>
</div>

