@extends('layouts.admin')

@section('title', 'Manajemen Resep - BrewPOS')
@section('page_title', 'Manajemen Resep')

@section('content')
<!-- Content Canvas -->
<main class="p-gutter flex-1">
    <div class="flex items-center justify-between mb-8">
        <div>
            <p class="text-body-md text-on-surface-variant">Menampilkan semua daftar menu minuman dan makanan untuk manajemen biaya produksi.</p>
        </div>
        <a href="{{ route('admin.recipes.create') }}" class="bg-primary-container text-white px-6 py-2 rounded-lg font-semibold text-body-md flex items-center gap-2 hover:opacity-90 active:scale-[0.98] transition-all">
            <span class="material-symbols-outlined">add</span>
            Tambah Data
        </a>
    </div>

    <!-- Search and Filter Bar -->
    <div class="bg-surface-container-lowest border-[0.5px] border-outline-variant rounded-xl p-4 mb-6 flex gap-4 items-center flex-wrap">
        <div class="relative flex-1 min-w-[200px]">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-[20px]">search</span>
            <input 
                name="search"
                value="{{ request('search') }}"
                class="w-full pl-10 pr-4 py-1.5 rounded-lg border-[0.5px] border-outline-variant bg-surface-container-lowest focus:ring-1 focus:ring-primary focus:border-primary outline-none text-body-md" 
                placeholder="Cari produk..." 
                type="text"
            />
        </div>
        
        <div class="flex items-center border-[0.5px] border-outline-variant rounded-lg overflow-hidden h-9 bg-surface-container-lowest">
            <form method="GET" class="flex items-center gap-0">
                <button 
                    type="submit" 
                    name="filter"
                    value="all"
                    @class([
                        'px-3 text-label-md font-semibold border-r-[0.5px] border-outline-variant transition-colors',
                        'bg-secondary-container text-on-secondary-container' => request('filter', 'all') === 'all',
                        'text-on-surface-variant hover:bg-surface-container-low' => request('filter', 'all') !== 'all'
                    ])
                >
                    Semua
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="with-recipe"
                    @class([
                        'px-3 text-label-md font-semibold border-r-[0.5px] border-outline-variant transition-colors',
                        'bg-secondary-container text-on-secondary-container' => request('filter') === 'with-recipe',
                        'text-on-surface-variant hover:bg-surface-container-low' => request('filter') !== 'with-recipe'
                    ])
                >
                    Ada Resep
                </button>
                <button 
                    type="submit" 
                    name="filter"
                    value="no-recipe"
                    @class([
                        'px-3 text-label-md font-semibold transition-colors',
                        'bg-secondary-container text-on-secondary-container' => request('filter') === 'no-recipe',
                        'text-on-surface-variant hover:bg-surface-container-low' => request('filter') !== 'no-recipe'
                    ])
                >
                    Belum Ada
                </button>
            </form>
        </div>
    </div>

    <!-- Recipe Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        @forelse($products as $product)
            @php
                $hasRecipe = $product->recipes()->exists();
                $recipeStatus = $hasRecipe ? 'Resep lengkap' : 'Belum ada resep';
                $ingredientCount = $product->recipes()->count();
                $hppTotal = $product->recipes()
                    ->join('raw_materials', 'product_recipes.raw_material_id', '=', 'raw_materials.id')
                    ->sum(\DB::raw('product_recipes.qty_used * raw_materials.cost_per_unit'));
            @endphp
            <div class="bg-surface-container-lowest border-[0.5px] border-outline-variant rounded-xl p-5 flex flex-col h-full hover:border-primary/40 transition-all group">
                <div class="flex justify-between items-start mb-4">
                    <!-- Product Image -->
                    <div class="w-16 h-16 rounded-lg bg-secondary-fixed flex items-center justify-center overflow-hidden">
                        @if($product->image)
                            <img alt="{{ $product->name }}" class="w-full h-full object-cover" src="{{ $product->image }}" />
                        @else
                            <span class="material-symbols-outlined text-outline">image</span>
                        @endif
                    </div>
                    <!-- Category Badge -->
                    <span class="bg-surface-container-high text-on-surface-variant px-2.5 py-1 rounded-md text-label-md">
                        {{ $product->category->name ?? 'Uncategorized' }}
                    </span>
                </div>
                
                <!-- Product Info -->
                <h3 class="font-headline-sm text-headline-sm mb-1">{{ $product->name }}</h3>
                <p class="text-body-md text-on-surface-variant mb-4">{{ $product->description ?? 'Tidak ada deskripsi' }}</p>
                
                <!-- Recipe Status and Details -->
                <div class="mt-auto space-y-4">
                    <div class="flex items-center gap-3">
                        @if($hasRecipe)
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded bg-[#E6F4F1] text-[#00695C] text-label-md font-bold">
                                <span class="material-symbols-outlined !text-[14px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                {{ $recipeStatus }}
                            </div>
                            <span class="text-label-md text-on-surface-variant">
                                {{ $ingredientCount }} bahan · <span class="font-bold">HPP Rp {{ number_format((int)$hppTotal, 0, ',', '.') }}</span>
                            </span>
                        @else
                            <div class="flex items-center gap-1.5 px-2 py-1 rounded bg-[#FFF8E1] text-[#854F0B] text-label-md font-bold">
                                <span class="material-symbols-outlined !text-[14px]" style="font-variation-settings: 'FILL' 1;">warning</span>
                                {{ $recipeStatus }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Action Button -->
                    <a 
                        href="{{ route('admin.recipes.edit', $product) }}" 
                        class="w-full py-2.5 px-4 rounded-lg border-[0.5px] border-primary text-primary font-bold text-body-md flex items-center justify-center gap-2 group-hover:bg-primary group-hover:text-white transition-all"
                    >
                        Atur Resep 
                        <span class="material-symbols-outlined !text-[18px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-body-md text-on-surface-variant">Tidak ada produk ditemukan.</p>
                <a href="{{ route('admin.recipes.create') }}" class="text-primary font-semibold hover:underline">Tambah produk baru</a>
            </div>
        @endforelse
    </div>

    <!-- Footer Stats -->
    <section class="mt-12 p-6 bg-surface-container-lowest border-[0.5px] border-outline-variant rounded-xl flex items-center justify-between flex-wrap gap-8">
        <div class="flex gap-12 flex-wrap">
            <div>
                <p class="text-label-sm text-secondary uppercase font-bold mb-1">Total Produk</p>
                <p class="text-headline-md font-bold text-primary">{{ $totalProducts }}</p>
            </div>
            <div>
                <p class="text-label-sm text-secondary uppercase font-bold mb-1">Sudah Beresep</p>
                <p class="text-headline-md font-bold text-[#00695C]">{{ $productsWithRecipe }}</p>
            </div>
            <div>
                <p class="text-label-sm text-secondary uppercase font-bold mb-1">HPP Rata-rata</p>
                <p class="text-headline-md font-bold text-primary">Rp {{ number_format($averageHpp, 0, ',', '.') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-4 flex-wrap">
            <button class="px-4 py-2 text-primary font-bold hover:bg-surface-container rounded-lg transition-colors">Export CSV</button>
            <button class="px-4 py-2 bg-secondary-container text-on-secondary-container font-bold rounded-lg hover:opacity-90 transition-all">Cetak Laporan</button>
        </div>
    </section>
</main>

<!-- Card Hover Animation -->
<script>
    const cards = document.querySelectorAll('.grid > div:not(.col-span-full)');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-4px)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
        });
    });
</script>
@endsection
