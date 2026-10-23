@extends('layouts.admin')

@section('title', 'Manajemen Resep — BrewPOS')
@section('page_title', 'Manajemen Resep Menu')

@section('content')
<div class="space-y-5">
    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Total Produk Menu</span>
                <i data-lucide="coffee" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-white tabular-nums">{{ $totalProducts }}</div>
                <p class="text-[11px] text-stone-400 mt-0.5">Produk aktif dalam katalog</p>
            </div>
        </div>

        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Resep Dikonfigurasi</span>
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-emerald-400 tabular-nums">{{ $productsWithRecipe }}</div>
                <p class="text-[11px] text-stone-400 mt-0.5">{{ $totalProducts - $productsWithRecipe }} menu belum beresep</p>
            </div>
        </div>

        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Rata-rata HPP</span>
                <i data-lucide="calculator" class="w-4 h-4 text-amber-500"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-amber-400 tabular-nums">
                    Rp {{ number_format($averageHpp, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-stone-400 mt-0.5">Biaya bahan pokok per porsi</p>
            </div>
        </div>
    </div>

    <!-- Header Action Bar & Filter -->
    <div class="surface-card p-4 sm:p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-sm font-semibold text-stone-200">Formulasi Resep Menu</h2>
            <p class="text-xs text-stone-400 mt-0.5">Kelola takaran bahan baku per menu untuk auto-deduction dan kalkulasi margin laba.</p>
        </div>
        <form method="GET" class="flex flex-1 sm:flex-initial flex-col sm:flex-row items-center gap-2.5 w-full sm:w-auto">
            <div class="relative flex-1 w-full sm:w-64">
                <input 
                    name="search"
                    value="{{ request('search') }}"
                    class="form-input pl-8" 
                    placeholder="Cari produk menu..." 
                    type="text"
                />
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-stone-500">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
            </div>
            
            <div class="inline-flex rounded-lg p-0.5 border border-[#2D2A26] bg-[#141312] shrink-0">
                <button 
                    type="submit" 
                    name="filter"
                    value="all"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors {{ request('filter', 'all') === 'all' ? 'bg-[#22201E] text-white' : 'text-stone-400 hover:text-stone-200' }}"
                >
                    Semua
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="with-recipe"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors {{ request('filter') === 'with-recipe' ? 'bg-[#22201E] text-white' : 'text-stone-400 hover:text-stone-200' }}"
                >
                    Ada Resep
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="no-recipe"
                    class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors {{ request('filter') === 'no-recipe' ? 'bg-[#22201E] text-white' : 'text-stone-400 hover:text-stone-200' }}"
                >
                    Belum Ada
                </button>
            </div>

            @if(request()->hasAny(['search', 'filter']))
                <a href="{{ route('admin.recipes.index') }}" class="btn btn-sm btn-outline shrink-0" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('admin.recipes.create') }}" class="btn btn-sm btn-primary shrink-0 w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Formulasi Resep Baru</span>
        </a>
    </div>

    <!-- Recipe Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($products as $product)
            @php
                $hasRecipe = $product->recipes->isNotEmpty();
                $hppTotal = $product->recipes->sum(function ($r) {
                    return (float) $r->qty_used * (float) ($r->rawMaterial?->cost_per_unit ?? 0);
                });
                $margin = $product->price > 0 && $hppTotal > 0 ? (($product->price - $hppTotal) / $product->price) * 100 : 0;
            @endphp
            <div class="surface-card p-4 flex flex-col justify-between hover:border-[#383430] transition-colors group">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-2 pb-2.5 border-b border-[#22201D]">
                        <div class="min-w-0">
                            <h3 class="font-bold text-sm text-white group-hover:text-amber-400 transition-colors truncate">
                                {{ $product->name }}
                            </h3>
                            <span class="text-[11px] text-stone-400">
                                {{ $product->category->name ?? 'Tanpa Kategori' }}
                            </span>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="font-mono text-xs font-bold text-stone-200 tabular-nums">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="block text-[10px] text-stone-500 font-mono">Harga Jual</span>
                        </div>
                    </div>

                    <!-- Ingredients List Preview -->
                    <div class="py-3 space-y-1.5 min-h-[70px]">
                        @if($hasRecipe)
                            <p class="text-[10px] font-semibold text-stone-400 uppercase tracking-wider">Komposisi Bahan:</p>
                            <div class="space-y-1">
                                @foreach($product->recipes->take(3) as $recipe)
                                    <div class="flex justify-between items-center text-[11px] text-stone-300">
                                        <span class="truncate pr-2">• {{ $recipe->rawMaterial->name ?? 'Bahan' }}</span>
                                        <span class="font-mono text-stone-400 shrink-0 tabular-nums">
                                            {{ (float) $recipe->qty_used }} {{ $recipe->rawMaterial->unit ?? '' }}
                                        </span>
                                    </div>
                                @endforeach
                                @if($product->recipes->count() > 3)
                                    <p class="text-[10px] text-stone-500 italic">+ {{ $product->recipes->count() - 3 }} bahan lainnya</p>
                                @endif
                            </div>
                        @else
                            <div class="h-full flex items-center gap-2 text-stone-500 text-xs py-2">
                                <i data-lucide="info" class="w-4 h-4 text-stone-600 shrink-0"></i>
                                <span>Belum ada bahan baku terhubung.</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer Summary & CTA -->
                <div class="pt-3 border-t border-[#22201D] space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            @if($hasRecipe)
                                <span class="badge badge-ready">
                                    <i data-lucide="check" class="w-3 h-3 text-emerald-400"></i>
                                    <span>Lengkap</span>
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i data-lucide="alert-circle" class="w-3 h-3 text-amber-400"></i>
                                    <span>Belum Beresep</span>
                                </span>
                            @endif
                        </div>
                        <div class="text-right font-mono text-xs">
                            <span class="text-stone-400">HPP: </span>
                            <strong class="{{ $hasRecipe ? 'text-stone-200' : 'text-stone-500' }} tabular-nums">
                                Rp {{ number_format((int)$hppTotal, 0, ',', '.') }}
                            </strong>
                            @if($hasRecipe && $margin > 0)
                                <span class="text-[10px] text-emerald-400 ml-1">({{ round($margin) }}%)</span>
                            @endif
                        </div>
                    </div>

                    <a 
                        href="{{ route('admin.recipes.edit', $product) }}" 
                        class="btn btn-sm btn-secondary w-full justify-between"
                    >
                        <span>Atur Komposisi Resep</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-stone-400"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 surface-card space-y-2">
                <i data-lucide="coffee" class="w-8 h-8 text-stone-600 mx-auto"></i>
                <p class="text-xs text-stone-400 font-medium">Tidak ada produk ditemukan sesuai filter.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
