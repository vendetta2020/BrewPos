@extends('layouts.admin')

@section('title', 'Daftar Produk — BrewPOS')
@section('page_title', 'Manajemen Produk')

@section('content')
<div class="space-y-5">
    <!-- Header Action Bar -->
    <div class="surface-card p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-sm font-bold text-white">Katalog Produk Menu</h2>
            <p class="text-xs text-stone-400 mt-0.5">Kelola seluruh produk, harga dasar, barcode SKU, dan varian ukuran.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </a>
    </div>

    <!-- Products Table Card -->
    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($products->count())
                <table class="w-full text-xs text-left text-stone-200">
                    <thead class="bg-[#141312] border-b border-[#2A2724] font-semibold text-stone-400">
                        <tr>
                            <th class="px-4 py-3">Nama Produk</th>
                            <th class="px-4 py-3">Barcode / SKU</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3 text-right">Harga Dasar</th>
                            <th class="px-4 py-3 text-center">Varian</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#22201D]">
                        @foreach($products as $product)
                            <tr class="hover:bg-[#1C1A18] transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-white">{{ $product->name }}</div>
                                    @if($product->description)
                                        <div class="text-[11px] text-stone-400 line-clamp-1 max-w-sm mt-0.5">{{ $product->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px]">
                                    @if($product->barcode)
                                        <span class="px-2 py-0.5 bg-[#141312] border border-[#2D2A26] rounded text-stone-300 font-semibold">
                                            {{ $product->barcode }}
                                        </span>
                                    @else
                                        <span class="text-stone-600">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-[#141312] text-stone-300 border border-[#2D2A26]">
                                        {{ $product->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold text-white tabular-nums">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-center font-mono whitespace-nowrap">
                                    @if($product->has_variants)
                                        <span class="badge badge-info">
                                            S, M, L
                                        </span>
                                    @else
                                        <span class="text-stone-500 text-[11px]">Standar</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($product->is_active)
                                        <span class="badge badge-ready">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">
                                            <span class="w-1.5 h-1.5 rounded-full bg-stone-500"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a 
                                            href="{{ route('admin.products.edit', $product) }}" 
                                            class="btn btn-sm btn-secondary"
                                            title="Edit Produk"
                                        >
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-stone-400"></i>
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
                                                class="btn btn-sm btn-destructive"
                                                title="Hapus Produk"
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
                <div class="p-12 text-center space-y-2">
                    <i data-lucide="package-open" class="w-8 h-8 text-stone-600 mx-auto"></i>
                    <h3 class="text-sm font-bold text-white">Belum Ada Produk</h3>
                    <p class="text-xs text-stone-400 max-w-sm mx-auto">Mulai tambahkan produk minuman atau makanan untuk ditampilkan di kasir POS.</p>
                </div>
            @endif
        </div>
        @if(method_exists($products, 'hasPages') && $products->hasPages())
            <div class="px-4 py-3 border-t border-[#2A2724]">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
