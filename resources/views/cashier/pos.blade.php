@extends('layouts.cashier')

@section('title', 'POS Kasir - BrewPos')

@section('content')
    <div class="w-full h-full relative">
        @livewire('pos.pos-interface')

        <!-- Order Success & Receipt Prompt Modal Component -->
        <div 
            id="receiptPromptModal"
            class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm transition-opacity"
            role="dialog"
            aria-modal="true"
            aria-labelledby="receiptModalTitle"
        >
            <div class="surface-card border border-stone-800 rounded-2xl shadow-2xl z-50 w-full max-w-md p-6 overflow-hidden relative text-stone-200 space-y-4">
                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 id="receiptModalTitle" class="text-sm font-bold text-white tracking-wide">Pesanan Berhasil Diproses</h3>
                            <p class="text-[11px] text-stone-400 mt-0.5">Transaksi selesai & tiket terkirim ke KDS Barista</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        onclick="closeReceiptModal()" 
                        class="text-stone-500 hover:text-stone-300 p-1 rounded-lg hover:bg-stone-800/50 transition-colors"
                        aria-label="Tutup"
                    >
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Order Details Card -->
                <div class="surface-card-inset p-3.5 rounded-xl border border-stone-800/80 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-semibold text-stone-500 block">Nomor Pesanan</span>
                        <span id="receiptModalOrderNumber" class="text-sm font-mono font-bold text-amber-400">TRX-XXXX</span>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-emerald text-[10px] px-2 py-0.5">Lunas</span>
                    </div>
                </div>

                <!-- Description Prompt -->
                <p class="text-xs text-stone-300 leading-relaxed">
                    Pesanan telah tersimpan di sistem. Apakah Anda ingin mencetak struk transaksi fisik sekarang?
                </p>

                <!-- Actions -->
                <div class="pt-2 flex items-center justify-end gap-2.5 border-t border-stone-800">
                    <button 
                        type="button" 
                        onclick="closeReceiptModal()" 
                        class="btn btn-secondary text-xs px-3.5 py-2 inline-flex items-center gap-1.5"
                    >
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-stone-400"></i>
                        <span>Selesai (Tanpa Struk)</span>
                    </button>

                    <button 
                        type="button" 
                        id="receiptModalPrintBtn"
                        onclick="confirmPrintReceipt()" 
                        class="btn btn-primary text-xs px-4 py-2 font-bold inline-flex items-center gap-1.5"
                    >
                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                        <span>Cetak Struk</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let activeReceiptOrderId = null;

    function openReceiptModal(orderId, orderNumber) {
        if (!orderId) return;
        activeReceiptOrderId = orderId;
        const modal = document.getElementById('receiptPromptModal');
        const orderNumEl = document.getElementById('receiptModalOrderNumber');
        const printBtn = document.getElementById('receiptModalPrintBtn');

        if (orderNumEl) {
            orderNumEl.innerText = orderNumber || ('#' + orderId);
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (window.lucide) lucide.createIcons();
            setTimeout(() => {
                printBtn?.focus();
            }, 50);
        }
    }

    function closeReceiptModal() {
        const modal = document.getElementById('receiptPromptModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        activeReceiptOrderId = null;
    }

    function confirmPrintReceipt() {
        if (activeReceiptOrderId) {
            window.open(`/cashier/orders/${activeReceiptOrderId}/receipt`, '_blank', 'width=400,height=600');
        }
        closeReceiptModal();
    }

    // Keyboard support: Escape closes modal, Enter triggers print if modal is open
    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('receiptPromptModal');
        if (modal && !modal.classList.contains('hidden')) {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeReceiptModal();
            } else if (e.key === 'Enter' && document.activeElement !== document.querySelector('button[onclick="closeReceiptModal()"]')) {
                e.preventDefault();
                confirmPrintReceipt();
            }
        }
    });

    window.addEventListener('checkout:success', event => {
        const detail = event.detail || {};
        const orderId = detail.order_id || (Array.isArray(detail) ? detail[0]?.order_id : null);
        const orderNumber = detail.order_number || (Array.isArray(detail) ? detail[0]?.order_number : null);
        openReceiptModal(orderId, orderNumber);
    });

    document.addEventListener('livewire:initialized', () => {
        if (window.Livewire) {
            Livewire.on('checkout:success', data => {
                const orderId = (data && typeof data === 'object') ? (data.order_id || (data[0] && data[0].order_id)) : null;
                const orderNumber = (data && typeof data === 'object') ? (data.order_number || (data[0] && data[0].order_number)) : null;
                if (orderId) {
                    openReceiptModal(orderId, orderNumber);
                }
            });
        }
    });
</script>
@endpush
