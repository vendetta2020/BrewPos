@extends('layouts.admin')

@section('title', 'Daftar Produk — BrewPOS')
@section('page_title', 'Manajemen Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-white tracking-tight">Katalog Produk Menu</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Kelola seluruh produk, harga dasar, barcode SKU, dan varian ukuran.</p>
        </div>
        <a 
            href="{{ route('admin.products.create') }}" 
            class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center gap-2 shrink-0"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </a>
    </div>

    <!-- Products Table Card -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            @if($products->count())
                <table class="w-full text-xs text-left text-stone-200">
                    <thead class="bg-[#141210] border-b border-[#2D2A26] font-semibold text-[#A8A29E] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Produk</th>
                            <th class="px-5 py-3.5">Barcode / SKU</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5 text-right">Harga Dasar</th>
                            <th class="px-5 py-3.5 text-center">Varian</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#272320]">
                        @foreach($products as $product)
                            <tr class="hover:bg-[#1E1C1A] transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-white">{{ $product->name }}</div>
                                    @if($product->description)
                                        <div class="text-[11px] text-[#A8A29E] line-clamp-1 max-w-xs">{{ $product->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px]">
                                    @if($product->barcode)
                                        <span class="px-2 py-0.5 bg-[#141312] border border-[#2D2A26] rounded text-amber-400 font-semibold">
                                            {{ $product->barcode }}
                                        </span>
                                    @else
                                        <span class="text-stone-600">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-[#141312] text-stone-300 border border-[#2D2A26]">
                                        {{ $product->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-white">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono">
                                    @if($product->has_variants)
                                        <span class="px-2 py-0.5 text-[10px] rounded bg-purple-500/10 text-purple-400 border border-purple-500/20 font-bold">
                                            S, M, L
                                        </span>
                                    @else
                                        <span class="text-stone-500 text-[11px]">Standar</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($product->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/25">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-stone-800 text-stone-400 border border-stone-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-stone-500"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a 
                                            href="{{ route('admin.products.edit', $product) }}" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-[#2D2A26] bg-[#141312] hover:bg-[#201D1A] text-stone-300 hover:text-white transition text-xs font-semibold"
                                        >
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-amber-400"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form 
                                            action="{{ route('admin.products.destroy', $product) }}" 
                                            method="POST" 
                                            class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition text-xs font-semibold"
                                            >
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-[#141312] border border-[#2D2A26] flex items-center justify-center text-stone-500 mx-auto">
                        <i data-lucide="package-open" class="w-6 h-6"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Belum Ada Produk</h3>
                    <p class="text-xs text-[#A8A29E] max-w-sm mx-auto">Mulai tambahkan produk minuman atau makanan untuk ditampilkan di kasir POS.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
