@extends('layouts.cashier')

@section('title', 'Riwayat Pesanan Sesi Ini — BrewPOS')

@section('content')
<div class="flex-1 flex flex-col h-full bg-[#0E0D0D] overflow-hidden text-[#F3F0EA]">
    <!-- Header Bar -->
    <div class="px-6 py-3.5 bg-[#141312] border-b border-stone-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 font-bold">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <h2 class="text-base font-bold text-white tracking-wide">
                    Riwayat Transaksi — Sesi #{{ $currentSession->id }}
                </h2>
            </div>
            <p class="text-xs text-stone-400 mt-0.5">
                Daftar seluruh transaksi yang diproses dalam shift kasir aktif saat ini.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pos') }}" class="btn btn-secondary text-xs inline-flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke POS</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <div class="px-6 pt-4">
        @if(session('success'))
            <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-xl text-xs font-semibold flex items-center gap-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Orders Table Container -->
    <div class="flex-1 overflow-y-auto p-6">
        @if($orders->isEmpty())
            <div class="h-64 flex flex-col items-center justify-center text-center p-6 border border-dashed border-stone-800 rounded-2xl bg-stone-900/30">
                <div class="w-12 h-12 rounded-xl surface-card flex items-center justify-center text-stone-500 mb-3">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
                <h4 class="text-sm font-semibold text-stone-200">Belum Ada Transaksi</h4>
                <p class="text-xs text-stone-500 mt-1">Transaksi yang Anda selesaikan di halaman POS akan tampil di sini.</p>
            </div>
        @else
            <div class="surface-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-stone-200">
                        <thead class="bg-stone-900/60 border-b border-stone-800 text-stone-400 uppercase font-mono tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3.5 whitespace-nowrap">No. Order</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Waktu</th>
                                <th class="px-4 py-3.5">Item Pesanan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Metode</th>
                                <th class="px-4 py-3.5 text-right whitespace-nowrap">Total Tagihan</th>
                                <th class="px-4 py-3.5 whitespace-nowrap">Status</th>
                                <th class="px-4 py-3.5 text-right whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-800">
                            @foreach($orders as $order)
                                @php
                                    $isCancelled = $order->status === 'cancelled';
                                @endphp
                                <tr class="hover:bg-stone-800/40 transition-colors {{ $isCancelled ? 'opacity-60 bg-stone-900/30' : '' }}">
                                    <td class="px-4 py-3.5 font-mono font-medium text-stone-200 whitespace-nowrap">
                                        #{{ $order->order_number }}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-stone-400 whitespace-nowrap">
                                        {{ $order->created_at->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <div class="space-y-1">
                                            @foreach($order->orderItems as $item)
                                                <div class="text-[11px] flex items-center gap-1.5">
                                                    <span class="text-stone-300">{{ $item->product->name ?? 'Produk' }}</span>
                                                    <span class="text-stone-400 font-mono font-medium">x{{ $item->quantity }}</span>
                                                    <span class="text-stone-500 text-[10px]">({{ $item->size }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5 uppercase font-mono text-[11px] text-stone-400 whitespace-nowrap">
                                        {{ $order->payment_method }}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono font-medium text-stone-200 text-right whitespace-nowrap">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5 whitespace-nowrap">
                                        @if($order->status === 'cancelled')
                                            <span class="badge badge-rose">Dibatalkan</span>
                                        @elseif($order->status === 'completed')
                                            <span class="badge badge-emerald">Selesai</span>
                                        @elseif($order->status === 'ready')
                                            <span class="badge badge-emerald">Siap</span>
                                        @elseif($order->status === 'processing')
                                            <span class="badge badge-amber">Diracik</span>
                                        @else
                                            <span class="badge badge-sky">{{ $order->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Print Receipt Button -->
                                            <a 
                                                href="{{ route('cashier.receipt', $order) }}" 
                                                target="_blank"
                                                class="btn btn-secondary px-2.5 py-1.5 text-xs inline-flex items-center gap-1.5"
                                                title="Cetak Struk"
                                            >
                                                <i data-lucide="printer" class="w-3.5 h-3.5 text-stone-400"></i>
                                                <span class="hidden sm:inline">Struk</span>
                                            </a>

                                            <!-- Void Button -->
                                            @if(!$isCancelled)
                                                <button 
                                                    type="button"
                                                    onclick="openVoidModal('{{ $order->id }}', '{{ $order->order_number }}')"
                                                    class="btn btn-destructive px-2.5 py-1.5 text-xs inline-flex items-center gap-1.5"
                                                    title="Batalkan Pesanan (Void)"
                                                >
                                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                    <span class="hidden sm:inline">Void</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Dialog for Void Confirmation -->
<div id="voidHistoryModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="surface-card w-full max-w-sm p-6 text-stone-200 space-y-4 shadow-2xl">
        <div class="flex items-center gap-2.5 text-rose-400">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-200">Konfirmasi Void Order</h3>
        </div>
        <p class="text-xs text-stone-400 leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span id="modalOrderNumber" class="font-mono font-medium text-stone-200"></span>? Bahan baku yang telah dipotong akan otomatis dikembalikan ke inventaris.
        </p>
        <form id="voidForm" method="POST" action="">
            @csrf
            <div>
                <label class="form-label mb-1.5">Alasan Pembatalan</label>
                <textarea 
                    name="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Salah input pesanan / pelanggan membatalkan" 
                    class="form-input text-xs"
                ></textarea>
            </div>
            <div class="flex gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeVoidModal()"
                    class="btn btn-secondary flex-1 py-2 text-xs"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="btn btn-destructive flex-1 py-2 text-xs"
                >
                    Ya, Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openVoidModal(orderId, orderNumber) {
        document.getElementById('modalOrderNumber').innerText = '#' + orderNumber;
        document.getElementById('voidForm').action = '/cashier/orders/' + orderId + '/void';
        const modal = document.getElementById('voidHistoryModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVoidModal() {
        const modal = document.getElementById('voidHistoryModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
