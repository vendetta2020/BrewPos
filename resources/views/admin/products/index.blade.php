@extends('layouts.admin')

@section('title', 'Daftar Produk — BrewPOS')
@section('page_title', 'Manajemen Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Katalog Produk Menu</h2>
            <p class="text-xs text-stone-500 mt-0.5">Kelola seluruh produk, harga dasar, barcode SKU, dan varian ukuran.</p>
        </div>
        <a 
            href="{{ route('admin.products.create') }}" 
            class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm flex items-center gap-2 shrink-0"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Produk</span>
        </a>
    </div>

    <!-- Products Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            @if($products->count())
                <table class="w-full text-xs text-left text-stone-700">
                    <thead class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600 uppercase tracking-wider">
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
                    <tbody class="divide-y divide-stone-100">
                        @foreach($products as $product)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-stone-900">{{ $product->name }}</div>
                                    @if($product->description)
                                        <div class="text-[11px] text-stone-400 line-clamp-1 max-w-xs">{{ $product->description }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 font-mono text-[11px]">
                                    @if($product->barcode)
                                        <span class="px-2 py-0.5 bg-stone-100 border border-stone-200 rounded text-stone-700 font-semibold">
                                            {{ $product->barcode }}
                                        </span>
                                    @else
                                        <span class="text-stone-300">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-stone-100 text-stone-700 border border-stone-200/60">
                                        {{ $product->category->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-stone-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($product->has_variants)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                            S / M / L
                                        </span>
                                    @else
                                        <span class="text-stone-400 font-mono text-[11px]">Standar</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.products.edit', $product) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-lg text-xs transition"
                                        title="Edit Produk"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-500"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')" 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition"
                                            title="Hapus Produk"
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
                        <i data-lucide="package-search" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-stone-900">Belum Ada Produk</h3>
                    <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Silakan tambahkan menu minuman atau makanan baru untuk memulai operasional POS.</p>
                    <div class="mt-4">
                        <a 
                            href="{{ route('admin.products.create') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow-xs"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Tambah Produk Sekarang</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
