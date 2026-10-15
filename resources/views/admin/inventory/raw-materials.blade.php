@extends('layouts.admin')

@section('title', 'Bahan Baku — BrewPOS')
@section('page_title', 'Manajemen Bahan Baku')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Katalog Bahan Baku & Stok Fisik</h2>
            <p class="text-xs text-stone-500 mt-0.5">Pantau kuantitas stok bahan mentah, batas minimum restock, dan harga perolehan HPP per unit.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('admin.restocks.create') }}" 
                class="px-3.5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-semibold rounded-xl border border-stone-200 transition flex items-center gap-1.5"
            >
                <i data-lucide="arrow-down-to-dot" class="w-4 h-4 text-amber-600"></i>
                <span>Catat Restock</span>
            </a>
            <a 
                href="{{ route('admin.raw-materials.create') }}" 
                class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm flex items-center gap-2 shrink-0"
            >
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Bahan Baku</span>
            </a>
        </div>
    </div>

    <!-- Materials Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            @if ($materials->count())
                <table class="w-full text-xs text-left text-stone-700">
                    <thead class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600 uppercase tracking-wider">
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
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($materials as $material)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-stone-900">
                                    {{ $material->name }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-700">
                                        {{ $material->category }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-stone-600">
                                    {{ $material->unit }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-stone-900">
                                    Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-xs {{ $material->stock_qty <= $material->min_stock_qty ? 'text-rose-600' : 'text-stone-900' }}">
                                    {{ number_format($material->stock_qty, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono text-stone-500">
                                    {{ number_format($material->min_stock_qty, 2, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($material->status === 'Kritis')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                            {{ $material->status }}
                                        </span>
                                    @elseif ($material->status === 'Hampir Habis')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $material->status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $material->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.raw-materials.edit', $material) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-lg text-xs transition"
                                        title="Edit Bahan"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-500"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form method="POST" action="{{ route('admin.raw-materials.destroy', $material) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition" 
                                            onclick="return confirm('Hapus bahan baku ini?')"
                                            title="Hapus Bahan"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i>
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
                    <div class="w-14 h-14 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-600">
                        <i data-lucide="boxes" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-stone-900">Belum Ada Bahan Baku</h3>
                    <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Tambahkan bahan mentah seperti Biji Kopi Espresso, Susu Segar, atau Gula Aren.</p>
                    <div class="mt-4">
                        <a 
                            href="{{ route('admin.raw-materials.create') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow-xs"
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
