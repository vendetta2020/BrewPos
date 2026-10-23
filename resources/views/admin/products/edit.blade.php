@extends('layouts.admin')

@section('title', 'Edit Produk — BrewPOS')
@section('page_title', 'Edit Produk')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">Perbarui Produk #{{ $product->id }}</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Ubah rincian menu, kode barcode, harga dasar, atau varian ukuran.</p>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Product Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Nama Produk <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $product->name) }}" 
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Barcode / SKU -->
            <div class="space-y-1.5">
                <label for="barcode" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Barcode / SKU Scanner (Opsional)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500">
                        <i data-lucide="scan-barcode" class="w-4 h-4"></i>
                    </span>
                    <input 
                        type="text" 
                        name="barcode" 
                        id="barcode" 
                        value="{{ old('barcode', $product->barcode) }}" 
                        placeholder="Contoh: 8991234567890" 
                        class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-medium text-white transition"
                    >
                </div>
                <p class="text-[11px] text-[#A8A29E]">Dapat dipindai langsung menggunakan hardware barcode scanner di kasir POS.</p>
                @error('barcode')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Category -->
            <div class="space-y-1.5">
                <label for="category_id" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Kategori Menu <span class="text-rose-400">*</span>
                </label>
                <x-dropdown-select 
                    name="category_id" 
                    id="category_id" 
                    :options="$categories" 
                    :selected="old('category_id', $product->category_id)"
                    placeholder="-- Pilih Kategori --"
                    required
                />
                @error('category_id')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Price and Variant Options -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="price" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Harga Dasar (Rp) <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500 font-mono font-bold text-xs">Rp</span>
                        <input 
                            type="number" 
                            step="100" 
                            name="price" 
                            id="price" 
                            value="{{ old('price', $product->price) }}" 
                            class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                            required
                        >
                    </div>
                    @error('price')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2.5 cursor-pointer select-none">
                        <input 
                            type="checkbox" 
                            name="has_variants" 
                            value="1" 
                            {{ old('has_variants', $product->has_variants) ? 'checked' : '' }} 
                            class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                        >
                        <span class="text-xs font-medium text-stone-300">Memiliki Varian Ukuran (S / M / L)</span>
                    </label>
                </div>
            </div>

            <!-- Variant Prices Box -->
            @if(old('has_variants', $product->has_variants))
                <div class="p-4 bg-[#141210] border border-[#2D2A26] rounded-xl space-y-3">
                    <h4 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                        <span>Harga per Ukuran Varian</span>
                    </h4>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-[#A8A29E] mb-1">Small (S)</label>
                            <input 
                                type="number" 
                                step="100" 
                                name="variants[S]" 
                                value="{{ old('variants.S', $product->variants->where('size', 'S')->first()?->price ?? $product->price) }}" 
                                class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] focus:border-amber-500 rounded-lg text-xs font-mono font-bold text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-[#A8A29E] mb-1">Medium (M)</label>
                            <input 
                                type="number" 
                                step="100" 
                                name="variants[M]" 
                                value="{{ old('variants.M', $product->variants->where('size', 'M')->first()?->price ?? $product->price) }}" 
                                class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] focus:border-amber-500 rounded-lg text-xs font-mono font-bold text-white"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-[#A8A29E] mb-1">Large (L)</label>
                            <input 
                                type="number" 
                                step="100" 
                                name="variants[L]" 
                                value="{{ old('variants.L', $product->variants->where('size', 'L')->first()?->price ?? $product->price) }}" 
                                class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] focus:border-amber-500 rounded-lg text-xs font-mono font-bold text-white"
                            >
                        </div>
                    </div>
                </div>
            @endif

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Deskripsi Menu
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="3" 
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                >{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Active Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-300">Status Aktif (Tampil di Layar Kasir POS)</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.products.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2"
                >
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
