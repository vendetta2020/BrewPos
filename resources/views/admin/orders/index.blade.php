@extends('layouts.admin')

@section('title', 'Riwayat Pesanan — BrewPOS')
@section('page_title', 'Riwayat Transaksi Pesanan')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col md:flex-row justify-between items-center gap-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari No. Order..." 
                    class="w-full pl-9 pr-4 py-2.5 text-xs bg-[#141312] border border-[#2D2A26] rounded-xl text-white placeholder-stone-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-500">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <select name="status" class="py-2.5 px-3 text-xs bg-[#141312] border border-[#2D2A26] text-white rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                <option value="" class="bg-[#181716] text-stone-400">Semua Status</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }} class="bg-[#181716] text-white">Paid</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }} class="bg-[#181716] text-white">Processing</option>
                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }} class="bg-[#181716] text-white">Ready</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }} class="bg-[#181716] text-white">Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }} class="bg-[#181716] text-white">Cancelled</option>
            </select>

            <button type="submit" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="px-3.5 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 border border-[#2D2A26] text-xs font-semibold rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2.5">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs font-semibold flex items-center gap-2.5">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-stone-200">
                <thead class="bg-[#141210] border-b border-[#2D2A26] font-semibold text-[#A8A29E] uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">No. Order</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Kasir / Sesi</th>
                        <th class="px-5 py-3.5">Item</th>
                        <th class="px-5 py-3.5">Metode</th>
                        <th class="px-5 py-3.5">Total</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#272320]">
                    @forelse($orders as $order)
                        @php $isCancelled = $order->status === 'cancelled'; @endphp
                        <tr class="hover:bg-[#1E1C1A] transition {{ $isCancelled ? 'opacity-50 bg-[#141312]/50' : '' }}">
                            <td class="px-5 py-4 font-mono font-bold text-amber-400">
                                #{{ $order->order_number }}
                            </td>
                            <td class="px-5 py-4 font-mono text-[#A8A29E] text-[11px]">
                                {{ $order->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-white">{{ $order->user->name ?? 'Kasir' }}</span>
                                <span class="block text-[11px] text-[#A8A29E] font-mono">Sesi #{{ $order->cashier_session_id }}</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <div class="space-y-1">
                                    @foreach($order->orderItems as $item)
                                        <div class="text-[11px]">
                                            <span class="font-medium text-stone-300">{{ $item->product->name ?? 'Produk' }}</span>
                                            <span class="text-amber-500 font-mono font-bold">x{{ $item->quantity }}</span>
                                            <span class="text-stone-500">({{ $item->size }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 uppercase font-mono font-semibold text-[11px] text-stone-300">
                                {{ $order->payment_method }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-white">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($order->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-[10px] font-bold">Dibatalkan</span>
                                @elseif($order->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold">Selesai</span>
                                @elseif($order->status === 'ready')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 text-[10px] font-bold">Siap</span>
                                @elseif($order->status === 'processing')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold">Diracik</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-sky-500/10 text-sky-400 border border-sky-500/20 text-[10px] font-bold">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a 
                                        href="{{ route('cashier.receipt', $order) }}" 
                                        target="_blank" 
                                        class="p-2 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 border border-[#2D2A26] rounded-lg transition" 
                                        title="Lihat Struk"
                                    >
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>
                                    @if(!$isCancelled)
                                        <button 
                                            type="button"
                                            onclick="openAdminVoidModal('{{ $order->id }}', '{{ $order->order_number }}')"
                                            class="p-2 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 rounded-lg transition"
                                            title="Batalkan Pesanan (Void)"
                                        >
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-[#A8A29E]">Tidak ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-4 border-t border-[#272320] bg-[#141210]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Dialog for Admin Void Confirmation -->
<div id="adminVoidModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
    <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center gap-2.5 text-rose-400">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <h3 class="text-sm font-bold uppercase tracking-wider">Konfirmasi Void Order</h3>
        </div>
        <p class="text-xs text-[#A8A29E] leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span id="modalOrderNumber" class="font-mono font-bold text-amber-400"></span>? Seluruh bahan baku resep yang telah dipotong akan otomatis dikembalikan ke inventori.
        </p>
        <form id="adminVoidForm" method="POST" action="">
            @csrf
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Alasan Pembatalan</label>
                <textarea 
                    name="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Salah input pesanan / permintaan pelanggan" 
                    class="w-full px-3 py-2 bg-[#141312] border border-[#2D2A26] text-white text-xs rounded-xl focus:border-rose-500 focus:ring-1 focus:ring-rose-500 placeholder-stone-600"
                ></textarea>
            </div>
            <div class="flex gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeAdminVoidModal()"
                    class="flex-1 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 font-bold text-xs rounded-xl border border-[#2D2A26] transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-rose-600/20 transition"
                >
                    Ya, Batalkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdminVoidModal(orderId, orderNumber) {
        document.getElementById('modalOrderNumber').innerText = '#' + orderNumber;
        document.getElementById('adminVoidForm').action = '/cashier/orders/' + orderId + '/void';
        const modal = document.getElementById('adminVoidModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeAdminVoidModal() {
        const modal = document.getElementById('adminVoidModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
