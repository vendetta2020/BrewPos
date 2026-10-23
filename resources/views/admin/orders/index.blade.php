@extends('layouts.admin')

@section('title', 'Riwayat Pesanan — BrewPOS')
@section('page_title', 'Riwayat Transaksi Pesanan')

@section('content')
<div class="space-y-6">
    <!-- Top Filter Bar -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari No. Order..." 
                    class="w-full pl-10 pr-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] rounded-xl text-xs text-white placeholder-stone-500 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition"
                >
            </div>

            <div class="w-full sm:w-56">
                <x-dropdown-select 
                    name="status"
                    :options="[
                        'paid' => 'Paid (Menunggu)',
                        'processing' => 'Processing (Sedang Dibuat)',
                        'ready' => 'Ready (Siap Diambil)',
                        'completed' => 'Completed (Selesai)',
                        'cancelled' => 'Cancelled (Dibatalkan)',
                    ]"
                    :selected="request('status')"
                    placeholder="Semua Status"
                />
            </div>

            <button 
                type="submit" 
                class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-2 shrink-0 cursor-pointer"
            >
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a 
                    href="{{ route('admin.orders.index') }}" 
                    class="px-3.5 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 hover:text-white border border-[#2D2A26] text-xs font-semibold rounded-xl transition flex items-center justify-center gap-1.5 shrink-0"
                    title="Reset Filter"
                >
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    <span>Reset</span>
                </a>
            @endif
        </form>

        <div class="text-xs font-mono text-stone-400 bg-[#141312] border border-[#2D2A26] px-3.5 py-2 rounded-xl flex items-center gap-2 self-start lg:self-auto shrink-0">
            <span class="text-stone-400">Total Transaksi:</span>
            <span class="font-bold text-amber-400">{{ $orders->total() }}</span>
            <span class="text-stone-400">Order</span>
        </div>
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
    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-xs text-stone-200">
                <thead class="bg-stone-900/60 border-b border-stone-800 font-semibold text-stone-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5 whitespace-nowrap">No. Order</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Tanggal</th>
                        <th class="px-5 py-3.5">Kasir / Sesi</th>
                        <th class="px-5 py-3.5">Item</th>
                        <th class="px-5 py-3.5">Metode</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Total</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-800">
                    @forelse($orders as $order)
                        @php $isCancelled = $order->status === 'cancelled'; @endphp
                        <tr class="hover:bg-stone-800/40 transition {{ $isCancelled ? 'opacity-50 bg-stone-900/30' : '' }}">
                            <td class="px-5 py-4 font-mono font-medium text-stone-200 whitespace-nowrap">
                                #{{ $order->order_number }}
                            </td>
                            <td class="px-5 py-4 font-mono text-stone-400 text-[11px] whitespace-nowrap">
                                {{ $order->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-medium text-stone-200">{{ $order->user->name ?? 'Kasir' }}</span>
                                <span class="block text-[11px] text-stone-500 font-mono">Sesi #{{ $order->cashier_session_id }}</span>
                            </td>
                            <td class="px-5 py-4 max-w-xs">
                                <div class="space-y-1">
                                    @foreach($order->orderItems as $item)
                                        <div class="text-[11px]">
                                            <span class="text-stone-300">{{ $item->product->name ?? 'Produk' }}</span>
                                            <span class="text-stone-400 font-mono font-medium">x{{ $item->quantity }}</span>
                                            <span class="text-stone-500 text-[10px]">({{ $item->size }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 uppercase font-mono text-[11px] text-stone-400 whitespace-nowrap">
                                {{ $order->payment_method }}
                            </td>
                            <td class="px-5 py-4 font-mono font-medium text-stone-200 text-right whitespace-nowrap">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
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
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a 
                                        href="{{ route('cashier.receipt', $order) }}" 
                                        target="_blank" 
                                        class="btn btn-secondary px-2.5 py-1.5 text-xs inline-flex items-center gap-1.5" 
                                        title="Lihat Struk"
                                    >
                                        <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                        <span class="hidden sm:inline">Struk</span>
                                    </a>
                                    @if(!$isCancelled)
                                        <button 
                                            type="button"
                                            onclick="openAdminVoidModal('{{ $order->id }}', '{{ $order->order_number }}')"
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
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-stone-400">Tidak ada riwayat transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="p-4 border-t border-stone-800 bg-stone-900/30">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Dialog for Admin Void Confirmation -->
<div id="adminVoidModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
    <div class="surface-card w-full max-w-sm p-6 space-y-4 shadow-2xl">
        <div class="flex items-center gap-2.5 text-rose-400">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-200">Konfirmasi Void Order</h3>
        </div>
        <p class="text-xs text-stone-400 leading-relaxed">
            Apakah Anda yakin ingin membatalkan pesanan <span id="modalOrderNumber" class="font-mono font-medium text-stone-200"></span>? Seluruh bahan baku resep yang telah dipotong akan otomatis dikembalikan ke inventori.
        </p>
        <form id="adminVoidForm" method="POST" action="">
            @csrf
            <div>
                <label class="form-label mb-1.5">Alasan Pembatalan</label>
                <textarea 
                    name="reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Salah input pesanan / permintaan pelanggan" 
                    class="form-input text-xs"
                ></textarea>
            </div>
            <div class="flex gap-2.5 pt-3">
                <button 
                    type="button" 
                    onclick="closeAdminVoidModal()"
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
