@extends('layouts.admin')

@section('title', 'Log Mutasi Stok — BrewPOS')
@section('page_title', 'Log Mutasi Stok Inventori')

@section('content')
<div class="space-y-6">
    <!-- Header Summary & Navigation -->
    <div class="bg-[#181716] rounded-2xl shadow-lg border border-[#2D2A26] p-5 sm:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-base sm:text-lg font-bold text-white tracking-tight">Audit Trail Mutasi Stok</h2>
            <p class="text-xs text-[#A8A29E] mt-1">
                Seluruh riwayat perubahan kuantitas fisik bahan baku (penjualan, restock, pembatalan order/void, dan koreksi stok manual).
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('admin.raw-materials.index') }}" class="px-3.5 py-2.5 border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4 text-stone-400"></i>
                <span>Daftar Bahan</span>
            </a>
            <a href="{{ route('admin.restocks.create') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md shadow-amber-600/20 transition flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Catat Restock</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-[#181716] rounded-2xl shadow-lg border border-[#2D2A26] p-5">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search Input -->
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Cari Catatan / Bahan</label>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Cari nomor order, supplier, atau alasan..."
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white placeholder-stone-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                >
            </div>

            <!-- Material Dropdown -->
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Bahan Baku</label>
                <select name="raw_material_id" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    <option value="" class="bg-[#181716] text-stone-400">Semua Bahan Baku</option>
                    @foreach($materials as $mat)
                        <option value="{{ $mat->id }}" {{ request('raw_material_id') == $mat->id ? 'selected' : '' }} class="bg-[#181716] text-white">
                            {{ $mat->name }} ({{ $mat->unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Dropdown -->
            <div>
                <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Tipe Mutasi</label>
                <select name="type" class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    <option value="" class="bg-[#181716] text-stone-400">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }} class="bg-[#181716] text-white">Masuk (Restock / Void)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }} class="bg-[#181716] text-white">Keluar (Penjualan)</option>
                    <option value="adjust" {{ request('type') === 'adjust' ? 'selected' : '' }} class="bg-[#181716] text-white">Koreksi (Penyesuaian Manual)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-4 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Filter</span>
                </button>
                @if(request()->hasAny(['search', 'raw_material_id', 'type']))
                    <a href="{{ route('admin.inventory.movements') }}" class="py-2.5 px-3 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 border border-[#2D2A26] text-xs font-medium rounded-xl transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Stock Movements Table -->
    <div class="bg-[#181716] rounded-2xl shadow-lg border border-[#2D2A26] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-200">
                <thead class="bg-[#141210] border-b border-[#2D2A26] text-[#A8A29E] font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Waktu</th>
                        <th class="px-5 py-3.5">Bahan Baku</th>
                        <th class="px-5 py-3.5 text-center">Tipe</th>
                        <th class="px-5 py-3.5 text-right">Jumlah</th>
                        <th class="px-5 py-3.5 text-right">Biaya / Unit</th>
                        <th class="px-5 py-3.5 text-right">Total Biaya</th>
                        <th class="px-5 py-3.5">Referensi / Catatan</th>
                        <th class="px-5 py-3.5">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#272320]">
                    @forelse($movements as $m)
                        <tr class="hover:bg-[#1E1C1A] transition-colors">
                            <td class="px-5 py-3.5 text-[#A8A29E] font-mono whitespace-nowrap">
                                {{ $m->created_at ? $m->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-white">
                                {{ $m->rawMaterial->name ?? 'Bahan Telah Dihapus' }}
                                <span class="text-[10px] font-normal text-[#A8A29E] block">{{ $m->rawMaterial->category ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3 text-emerald-400"></i>
                                        <span>Masuk</span>
                                    </span>
                                @elseif($m->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-rose-400"></i>
                                        <span>Keluar</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        <i data-lucide="refresh-cw" class="w-3 h-3 text-amber-400"></i>
                                        <span>Koreksi</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold {{ $m->type === 'in' ? 'text-emerald-400' : ($m->type === 'out' ? 'text-rose-400' : 'text-amber-400') }} whitespace-nowrap">
                                {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '-' : '±') }}
                                {{ number_format($m->qty, 2, ',', '.') }} {{ $m->rawMaterial->unit ?? '' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-[#A8A29E] whitespace-nowrap">
                                Rp {{ number_format($m->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-semibold text-white whitespace-nowrap">
                                Rp {{ number_format($m->total_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-stone-300 max-w-xs">
                                <span class="line-clamp-2 text-xs">{{ $m->note ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-stone-300 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-[#272320] text-amber-400 flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($m->creator->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span class="text-xs">{{ $m->creator->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-[#A8A29E]">
                                <div class="w-12 h-12 bg-[#141210] border border-[#2D2A26] rounded-2xl flex items-center justify-center mx-auto mb-2 text-amber-500">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <p class="font-semibold text-white">Belum ada catatan mutasi stok.</p>
                                <p class="text-xs text-[#A8A29E] mt-1">Mutasi akan otomatis tercatat saat terjadi penjualan, restock, pembatalan, atau penyesuaian stok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-[#272320] bg-[#141210] flex items-center justify-between">
                <span class="text-xs text-[#A8A29E]">
                    Menampilkan {{ $movements->firstItem() ?? 0 }}-{{ $movements->lastItem() ?? 0 }} dari {{ $movements->total() }} log mutasi
                </span>
                <div>
                    {{ $movements->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
