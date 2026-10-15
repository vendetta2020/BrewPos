@extends('layouts.admin')

@section('title', 'Manajemen Resep — BrewPOS')
@section('page_title', 'Manajemen Resep Menu')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Header -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-white tracking-tight">Formulasi Resep Menu & Biaya Produksi (HPP)</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Kelola takaran bahan baku per menu untuk auto-deduction saat checkout dan kalkulasi margin laba.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs text-[#A8A29E]">
                Total Menu: <strong class="font-mono text-white">{{ $totalProducts }}</strong>
            </span>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 shadow-lg flex flex-col sm:flex-row gap-3 items-center justify-between">
        <form method="GET" class="flex flex-1 flex-col sm:flex-row gap-3 w-full">
            <div class="relative flex-1">
                <input 
                    name="search"
                    value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white placeholder-stone-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                    placeholder="Cari produk menu..." 
                    type="text"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
            </div>
            
            <div class="flex items-center rounded-xl overflow-hidden border border-[#2D2A26] bg-[#141312]">
                <button 
                    type="submit" 
                    name="filter"
                    value="all"
                    class="px-3.5 py-2 text-xs font-semibold border-r border-[#2D2A26] transition {{ request('filter', 'all') === 'all' ? 'bg-amber-600 text-white' : 'text-stone-300 hover:text-white' }}"
                >
                    Semua
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="with-recipe"
                    class="px-3.5 py-2 text-xs font-semibold border-r border-[#2D2A26] transition {{ request('filter') === 'with-recipe' ? 'bg-amber-600 text-white' : 'text-stone-300 hover:text-white' }}"
                >
                    Ada Resep
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="no-recipe"
                    class="px-3.5 py-2 text-xs font-semibold transition {{ request('filter') === 'no-recipe' ? 'bg-amber-600 text-white' : 'text-stone-300 hover:text-white' }}"
                >
                    Belum Ada
                </button>
            </div>

            @if(request()->hasAny(['search', 'filter']))
                <a href="{{ route('admin.recipes.index') }}" class="px-3 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 border border-[#2D2A26] text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Recipe Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($products as $product)
            @php
                $hasRecipe = $product->recipes()->exists();
                $recipeStatus = $hasRecipe ? 'Resep Lengkap' : 'Belum Ada Resep';
                $ingredientCount = $product->recipes()->count();
                $hppTotal = $product->recipes()
                    ->join('raw_materials', 'product_recipes.raw_material_id', '=', 'raw_materials.id')
                    ->sum(\DB::raw('product_recipes.qty_used * raw_materials.cost_per_unit'));
            @endphp
            <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl p-5 flex flex-col h-full hover:border-amber-500/40 transition shadow-lg group">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="coffee" class="w-5 h-5"></i>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-[#141312] text-stone-300 border border-[#2D2A26]">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
                
                <!-- Product Info -->
                <h3 class="text-base font-bold text-white mb-1 group-hover:text-amber-400 transition">{{ $product->name }}</h3>
                <p class="text-xs text-[#A8A29E] line-clamp-2 mb-4">{{ $product->description ?? 'Menu minuman racikan BrewPOS' }}</p>
                
                <!-- Recipe Status and Details -->
                <div class="mt-auto space-y-4 pt-3 border-t border-[#272320]">
                    <div class="flex items-center justify-between">
                        @if($hasRecipe)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $recipeStatus }}</span>
                            </span>
                            <span class="text-xs font-mono text-white">
                                HPP: <strong class="text-amber-400">Rp {{ number_format((int)$hppTotal, 0, ',', '.') }}</strong>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                                <span>{{ $recipeStatus }}</span>
                            </span>
                            <span class="text-xs text-stone-500 font-mono">0 Bahan</span>
                        @endif
                    </div>
                    
                    <!-- Action Button -->
                    <a 
                        href="{{ route('admin.recipes.edit', $product) }}" 
                        class="w-full py-2.5 px-4 rounded-xl border border-[#2D2A26] bg-[#24211E] hover:bg-amber-600 hover:border-amber-600 text-stone-200 hover:text-white font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition"
                    >
                        <span>Atur Resep Komposisi</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-[#181716] border border-[#2D2A26] rounded-2xl">
                <p class="text-xs text-[#A8A29E]">Tidak ada produk ditemukan.</p>
            </div>
        @endforelse
    </div>

    <!-- Summary KPI Bar -->
    <div class="p-5 bg-[#181716] border border-[#2D2A26] rounded-2xl flex items-center justify-between flex-wrap gap-6 shadow-lg">
        <div class="flex gap-8 sm:gap-12 flex-wrap">
            <div>
                <p class="text-[11px] text-[#A8A29E] uppercase font-bold tracking-wider mb-0.5">Total Menu</p>
                <p class="text-2xl font-bold font-mono text-white">{{ $totalProducts }}</p>
            </div>
            <div>
                <p class="text-[11px] text-[#A8A29E] uppercase font-bold tracking-wider mb-0.5">Sudah Beresep</p>
                <p class="text-2xl font-bold font-mono text-emerald-400">{{ $productsWithRecipe }}</p>
            </div>
            <div>
                <p class="text-[11px] text-[#A8A29E] uppercase font-bold tracking-wider mb-0.5">HPP Rata-rata</p>
                <p class="text-2xl font-bold font-mono text-amber-400">Rp {{ number_format($averageHpp, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
