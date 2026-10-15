@extends('layouts.admin')

@section('title', 'Manajemen Resep — BrewPOS')
@section('page_title', 'Manajemen Resep Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Formulasi Resep & Bahan Baku Menu</h2>
            <p class="text-xs text-stone-500 mt-0.5">Konfigurasikan komposisi takaran bahan baku per produk untuk auto-deduction dan perhitungan HPP.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-stone-500">
                Total Produk Menu: <strong class="font-mono text-stone-900">{{ $products->count() }}</strong>
            </span>
        </div>
    </div>

    <!-- Products List Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            @if ($products->count())
                <table class="w-full text-xs text-left text-stone-700">
                    <thead class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Produk Menu</th>
                            <th class="px-5 py-3.5">Kategori</th>
                            <th class="px-5 py-3.5 text-right">Harga Jual</th>
                            <th class="px-5 py-3.5 text-center">Varian</th>
                            <th class="px-5 py-3.5 text-center">Status Resep</th>
                            <th class="px-5 py-3.5 text-center">Item Bahan</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($products as $product)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-stone-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600">
                                            <i data-lucide="coffee" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-stone-100 text-stone-700">
                                        {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold text-stone-900">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($product->has_variants)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                                            S / M / L
                                        </span>
                                    @else
                                        <span class="text-stone-400 font-mono text-[11px]">Standar</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($product->has_recipe)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="check-circle" class="w-3 h-3 text-emerald-500"></i>
                                            Terkonfigurasi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <i data-lucide="alert-circle" class="w-3 h-3 text-amber-500"></i>
                                            Belum Ada Resep
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center font-mono font-bold text-stone-800">
                                    {{ $product->recipes_count ?? $product->recipes()->count() }} Bahan
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a 
                                        href="{{ route('admin.recipes.show', $product) }}" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-600 hover:bg-amber-500 text-white font-semibold rounded-lg text-xs transition shadow-2xs"
                                    >
                                        <i data-lucide="sliders-horizontal" class="w-3.5 h-3.5"></i>
                                        <span>Atur Resep</span>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Clean Empty State -->
                <div class="text-center py-12 px-4">
                    <div class="w-14 h-14 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-600">
                        <i data-lucide="book-open" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-stone-900">Belum Ada Menu</h3>
                    <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Tambahkan produk menu terlebih dahulu untuk dapat mengatur resep komposisi bahan baku.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
