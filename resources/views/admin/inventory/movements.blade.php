@extends('layouts.admin')

@section('title', 'Log Mutasi Stok - BrewPOS')
@section('page_title', 'Log Mutasi Stok Inventori')

@section('content')
<div class="space-y-6">
    <!-- Header Summary & Navigation -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Audit Trail Mutasi Stok</h2>
            <p class="text-xs text-gray-500 mt-1">
                Seluruh riwayat perubahan kuantitas fisik bahan baku (penjualan, restock, pembatalan order/void, dan koreksi stok manual).
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.raw-materials.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="boxes" class="w-4 h-4"></i>
                <span>Daftar Bahan</span>
            </a>
            <a href="{{ route('admin.restocks.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Catat Restock</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.inventory.movements') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
            <!-- Search Input -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Cari Catatan / Bahan</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari nomor order, supplier, atau alasan..."
                        class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Material Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Bahan Baku</label>
                <select name="raw_material_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Semua Bahan Baku</option>
                    @foreach($materials as $mat)
                        <option value="{{ $mat->id }}" {{ request('raw_material_id') == $mat->id ? 'selected' : '' }}>
                            {{ $mat->name }} ({{ $mat->unit }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Type Dropdown -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Tipe Mutasi</label>
                <select name="type" class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk (Restock / Void)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar (Penjualan)</option>
                    <option value="adjust" {{ request('type') === 'adjust' ? 'selected' : '' }}>Koreksi (Penyesuaian Manual)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-gray-900 hover:bg-gray-800 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan Filter</span>
                </button>
                @if(request()->hasAny(['search', 'raw_material_id', 'type']))
                    <a href="{{ route('admin.inventory.movements') }}" class="py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium rounded-xl transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Stock Movements Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold uppercase tracking-wider text-[11px]">
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
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-5 py-3.5 text-gray-500 font-mono whitespace-nowrap">
                                {{ $m->created_at ? $m->created_at->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-gray-900">
                                {{ $m->rawMaterial->name ?? 'Bahan Telah Dihapus' }}
                                <span class="text-[10px] font-normal text-gray-400 block">{{ $m->rawMaterial->category ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-down-left" class="w-3 h-3 text-emerald-600"></i>
                                        <span>Masuk</span>
                                    </span>
                                @elseif($m->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="arrow-up-right" class="w-3 h-3 text-rose-600"></i>
                                        <span>Keluar</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="refresh-cw" class="w-3 h-3 text-amber-600"></i>
                                        <span>Koreksi</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-bold {{ $m->type === 'in' ? 'text-emerald-600' : ($m->type === 'out' ? 'text-rose-600' : 'text-amber-600') }} whitespace-nowrap">
                                {{ $m->type === 'in' ? '+' : ($m->type === 'out' ? '-' : '±') }}
                                {{ number_format($m->qty, 2, ',', '.') }} {{ $m->rawMaterial->unit ?? '' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-gray-600 whitespace-nowrap">
                                Rp {{ number_format($m->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-semibold text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($m->total_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-gray-700 max-w-xs">
                                <span class="line-clamp-2">{{ $m->note ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-700">
                                        {{ strtoupper(substr($m->creator->name ?? 'A', 0, 1)) }}
                                    </div>
                                    <span>{{ $m->creator->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="w-12 h-12 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-2 text-gray-400">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <p class="font-semibold text-gray-700">Belum ada catatan mutasi stok.</p>
                                <p class="text-xs text-gray-400 mt-1">Mutasi akan otomatis tercatat saat terjadi penjualan, restock, pembatalan, atau penyesuaian stok.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
                <span class="text-xs text-gray-500">
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
