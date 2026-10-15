@extends('layouts.cashier')

@section('title', 'Riwayat Pesanan Sesi Ini — BrewPOS')

@section('content')
<div class="flex-1 flex flex-col h-full bg-[#0E0D0D] overflow-hidden text-[#F3F0EA]">
    <!-- Header Bar -->
    <div class="px-6 py-4 bg-[#141312] border-b border-[#2D2A26] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shrink-0">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 font-bold">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </div>
                <h2 class="text-lg font-bold text-white tracking-wide">
                    Riwayat Transaksi — Sesi #{{ $currentSession->id }}
                </h2>
            </div>
            <p class="text-xs text-[#A8A29E] mt-1">
                Daftar seluruh transaksi yang diproses dalam shift kasir aktif saat ini (SRS POS-10).
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('pos') }}" class="px-4 py-2 bg-[#242220] hover:bg-[#2D2A26] text-stone-200 text-xs font-bold rounded-xl border border-[#383430] transition-colors flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
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
            <div class="h-64 flex flex-col items-center justify-center text-center p-6 border border-dashed border-[#2D2A26] rounded-2xl bg-[#141312]/40">
                <div class="w-14 h-14 rounded-2xl bg-[#181716] border border-[#2D2A26] flex items-center justify-center text-[#57534E] mb-3">
                    <i data-lucide="shopping-bag" class="w-7 h-7"></i>
                </div>
                <h4 class="text-sm font-bold text-[#F3F0EA]">Belum Ada Transaksi</h4>
                <p class="text-xs text-[#78716C] mt-1">Transaksi yang Anda selesaikan di halaman POS akan tampil di sini.</p>
            </div>
        @else
            <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl overflow-hidden shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-[#E7E5E4]">
                        <thead class="bg-[#141312] border-b border-[#2D2A26] text-[#A8A29E] uppercase font-mono tracking-wider text-[11px]">
                            <tr>
                                <th class="px-4 py-3.5">No. Order</th>
                                <th class="px-4 py-3.5">Waktu</th>
                                <th class="px-4 py-3.5">Item Pesanan</th>
                                <th class="px-4 py-3.5">Metode</th>
                                <th class="px-4 py-3.5">Total Tagihan</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#242220]">
                            @foreach($orders as $order)
                                @php
                                    $isCancelled = $order->status === 'cancelled';
                                @endphp
                                <tr class="hover:bg-[#201E1B] transition-colors {{ $isCancelled ? 'opacity-60 bg-black/20' : '' }}">
                                    <td class="px-4 py-3.5 font-mono font-bold text-white">
                                        #{{ $order->order_number }}
                                    </td>
                                    <td class="px-4 py-3.5 font-mono text-[#A8A29E]">
                                        {{ $order->created_at->format('H:i') }}
                                    </td>
                                    <td class="px-4 py-3.5 max-w-xs">
                                        <div class="space-y-1">
                                            @foreach($order->orderItems as $item)
                                                <div class="text-[11px] flex items-center gap-1.5">
                                                    <span class="text-white font-medium">{{ $item->product->name ?? 'Produk' }}</span>
                                                    <span class="text-amber-400 font-mono font-bold">x{{ $item->quantity }}</span>
                                                    <span class="text-[#78716C] text-[10px]">({{ $item->size }})</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="px-2 py-0.5 rounded border text-[10px] font-mono font-bold uppercase {{ $order->payment_method === 'cash' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                            {{ $order->payment_method }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 font-mono font-extrabold text-amber-400">
                                        Rp {{ number_format($order->total, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if($order->status === 'cancelled')
                                            <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-mono font-bold uppercase">
                                                Dibatalkan
                                            </span>
                                        @elseif($order->status === 'completed')
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-mono font-bold uppercase">
                                                Selesai
                                            </span>
                                        @elseif($order->status === 'ready')
                                            <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-mono font-bold uppercase">
                                                Siap
                                            </span>
                                        @elseif($order->status === 'processing')
                                            <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-mono font-bold uppercase">
                                                Diracik
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20 text-[10px] font-mono font-bold uppercase">
                                                {{ $order->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Print Receipt Button -->
                                            <a 
                                                href="{{ route('cashier.receipt', $order) }}" 
                                                target="_blank"
                                                class="px-2.5 py-1.5 bg-[#242220] hover:bg-[#2D2A26] text-stone-300 hover:text-white rounded-lg border border-[#383430] transition-colors flex items-center gap-1 font-semibold"
                                                title="Cetak Struk"
                                            >
                                                <i data-lucide="printer" class="w-3.5 h-3.5 text-amber-400"></i>
                                                <span>Struk</span>
                                            </a>

                                            <!-- Void Button -->
                                            @if(!$isCancelled)
                                                <button 
                                                    type="button"
                                                    onclick="openVoidModal('{{ $order->id }}', '{{ $order->order_number }}')"
                                                    class="px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded-lg border border-rose-500/30 transition-colors flex items-center gap-1 font-semibold"
                                                    title="Batalkan Pesanan (Void)"
                                                >
                                                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i>
                                                    <span>Void</span>
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
    <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl shadow-2xl w-full max-w-sm p-6 text-[#F3F0EA] space-y-4">
        <div class="flex items-center gap-2.5 text-rose-400">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <h3 class="text-sm font-bold uppercase tracking-wider">Konfirmasi Void Order</h3>
        </div>
        <p class="text-xs text-[#A8A29E] leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span id="modalOrderNumber" class="font-mono font-bold text-white"></span>? Bahan baku yang telah dipotong akan otomatis dikembalikan ke stok inventaris.
        </p>
        <form id="voidForm" method="POST" action="">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Alasan Pembatalan</label>
                <textarea 
                    name="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Salah input pesanan / pelanggan membatalkan" 
                    class="w-full px-3 py-2 bg-[#141312] border border-[#2D2A26] text-white font-sans text-xs rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 placeholder-[#57534E]"
                ></textarea>
            </div>
            <div class="flex gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeVoidModal()"
                    class="flex-1 py-2 bg-[#242220] hover:bg-[#2D2A26] text-stone-300 font-bold text-xs rounded-xl border border-[#383430] transition-colors"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-lg hover:shadow-rose-600/20 transition-all"
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
