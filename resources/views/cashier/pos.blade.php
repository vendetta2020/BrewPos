@extends('layouts.cashier')

@section('title', 'POS Kasir - BrewPos')

@section('content')
    <div class="w-full h-full">
        @livewire('pos.pos-interface')
    </div>
@endsection

@push('scripts')
<script>
    function promptReceipt(orderId, orderNumber) {
        if (!orderId) return;
        if (confirm(`Order ${orderNumber || ''} berhasil diproses! Cetak struk sekarang?`)) {
            window.open(`/cashier/orders/${orderId}/receipt`, '_blank', 'width=400,height=600');
        }
    }

    window.addEventListener('checkout:success', event => {
        const detail = event.detail || {};
        const orderId = detail.order_id || (Array.isArray(detail) ? detail[0]?.order_id : null);
        const orderNumber = detail.order_number || (Array.isArray(detail) ? detail[0]?.order_number : null);
        promptReceipt(orderId, orderNumber);
    });
</script>
@endpush
