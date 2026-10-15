@extends('layouts.admin')

@section('title', 'Bahan Baku — BrewPOS')
@section('page_title', 'Manajemen Bahan Baku')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-white tracking-tight">Katalog Bahan Baku & Stok Fisik</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Pantau kuantitas stok bahan mentah, batas minimum restock, dan harga perolehan HPP per unit.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('admin.restocks.create') }}" 
                class="px-3.5 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 text-xs font-semibold rounded-xl border border-[#2D2A26] transition flex items-center gap-1.5"
            >
                <i data-lucide="arrow-down-to-dot" class="w-4 h-4 text-amber-500"></i>
                <span>Catat Restock</span>
            </a>
            <a 
                href="{{ route('admin.raw-materials.create') }}" 
                class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center gap-2 shrink-0"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Bahan Baku</span>
            </a>
        </div>
    </div>

    <!-- Materials Table Card -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            @if ($materials->count())
                <table class="w-full text-xs text-left text-stone-200">
                    <thead class="bg-[#141210] border-b border-[#2D2A26] font-semibold text-[#A8A29E] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Bahan</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5">Satuan</th>
                            <th class="px-5 py-3.5 text-right">Harga / Unit</th>
                            <th class="px-5 py-3.5 text-right">Stok Fisik</th>
                            <th class="px-5 py-3.5 text-right">Min Stok</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#272320]">
                        @foreach ($materials as $material)
                            <tr class="hover:bg-[#1E1C1A] transition-colors">
                                <td class="px-5 py-3.5 font-bold text-white">
                                    {{ $material->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#141312] text-stone-300 border border-[#2D2A26]">
                                        {{ $material->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-stone-400">
                                    {{ $material->unit }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-white">
                                    Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-xs {{ $material->stock_qty <= $material->min_stock_qty ? 'text-rose-400' : 'text-stone-200' }}">
                                    {{ number_format($material->stock_qty, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-stone-500">
                                    {{ number_format($material->min_stock_qty, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($material->status === 'Kritis')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            {{ $material->status }}
                                        </span>
                                    @elseif ($material->status === 'Hampir Habis')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $material->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $material->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.raw-materials.edit', $material) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 border border-[#2D2A26] font-semibold rounded-lg text-xs transition"
                                        title="Edit Bahan"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-400"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.raw-materials.destroy', $material) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold rounded-lg text-xs transition" 
                                            onclick="return confirm('Hapus bahan baku ini?')"
                                            title="Hapus Bahan"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-400"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Clean Empty State -->
                <div class="text-center py-12 px-4">
                    <div class="w-14 h-14 bg-[#141210] border border-[#2D2A26] rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-500">
                        <i data-lucide="boxes" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Belum Ada Bahan Baku</h3>
                    <p class="text-xs text-[#A8A29E] mt-1 max-w-sm mx-auto">Tambahkan bahan mentah seperti Biji Kopi Espresso, Susu Segar, atau Gula Aren.</p>
                    <div class="mt-4">
                        <a 
                            href="{{ route('admin.raw-materials.create') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow-md shadow-amber-600/20"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Tambah Bahan Baku Sekarang</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
