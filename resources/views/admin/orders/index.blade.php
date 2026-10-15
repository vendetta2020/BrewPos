@extends('layouts.admin')

@section('title', 'Riwayat Pesanan - BrewPOS')
@section('page_title', 'Riwayat Transaksi Pesanan')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative w-full sm:w-64">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari No. Order..." 
                    class="w-full pl-9 pr-4 py-2 text-xs border border-gray-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <select name="status" class="py-2 px-3 text-xs border border-gray-200 rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                <option value="">Semua Status</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Ready</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.orders.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-xl transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-xs font-semibold flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 border-b border-gray-200 font-semibold text-gray-600">
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
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        @php $isCancelled = $order->status === 'cancelled'; @endphp
                        <tr class="hover:bg-gray-50/60 transition {{ $isCancelled ? 'opacity-60 bg-gray-50/30' : '' }}">
                            <td class="px-5 py-4 font-mono font-bold text-gray-900">
                                #{{ $order->order_number }}
                            </td>
                            <td class="px-5 py-4 font-mono text-gray-500">
                                {{ $order->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-gray-900">{{ $order->user->name ?? 'Kasir' }}</span>
                                <span class="block text-[11px] text-gray-400 font-mono">Sesi #{{ $order->cashier_session_id }}</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <div class="space-y-1">
                                    @foreach($order->orderItems as $item)
                                        <div class="text-[11px]">
                                            <span class="font-medium text-gray-800">{{ $item->product->name ?? 'Produk' }}</span>
                                            <span class="text-amber-600 font-mono font-bold">x{{ $item->quantity }}</span>
                                            <span class="text-gray-400">({{ $item->size }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 uppercase font-mono font-semibold text-[11px]">
                                {{ $order->payment_method }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-gray-900">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4">
                                @if($order->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-[10px] font-bold">Dibatalkan</span>
                                @elseif($order->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[10px] font-bold">Selesai</span>
                                @elseif($order->status === 'ready')
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold">Siap</span>
                                @elseif($order->status === 'processing')
                                    <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-[10px] font-bold">Diracik</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">{{ $order->status }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a 
                                        href="{{ route('cashier.receipt', $order) }}" 
                                        target="_blank" 
                                        class="p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition" 
                                        title="Lihat Struk"
                                    >
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </a>
                                    @if(!$isCancelled)
                                        <button 
                                            type="button"
                                            onclick="openAdminVoidModal('{{ $order->id }}', '{{ $order->order_number }}')"
                                            class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition"
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
                            <td colspan="8" class="px-5 py-10 text-center text-gray-400">Tidak ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Modal Dialog for Admin Void Confirmation -->
<div id="adminVoidModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center gap-2.5 text-red-600">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            <h3 class="text-sm font-bold uppercase tracking-wider">Konfirmasi Void Order</h3>
        </div>
        <p class="text-xs text-gray-600 leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span id="modalOrderNumber" class="font-mono font-bold text-gray-900"></span>? Seluruh bahan baku resep yang telah dipotong akan otomatis dikembalikan ke inventori.
        </p>
        <form id="adminVoidForm" method="POST" action="">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Alasan Pembatalan</label>
                <textarea 
                    name="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Salah input pesanan / permintaan pelanggan" 
                    class="w-full px-3 py-2 border border-gray-200 text-gray-900 text-xs rounded-xl focus:border-red-500 focus:ring-1 focus:ring-red-500 placeholder-gray-400"
                ></textarea>
            </div>
            <div class="flex gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeAdminVoidModal()"
                    class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-xs rounded-xl transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 py-2 bg-red-600 hover:bg-red-500 text-white font-bold text-xs rounded-xl shadow-lg hover:shadow-red-600/20 transition"
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
