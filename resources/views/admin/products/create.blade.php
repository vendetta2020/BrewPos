@extends('layouts.admin')

@section('title', 'Tambah Produk — BrewPOS')
@section('page_title', 'Tambah Produk Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">Informasi Produk Baru</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Isi rincian nama menu, barcode SKU, kategori, dan harga jual.</p>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Product Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Nama Produk <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Caramel Macchiato" 
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
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
                        value="{{ old('barcode') }}" 
                        placeholder="Contoh: 8991234567890" 
                        class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-medium text-white placeholder-stone-600 transition"
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
                <select 
                    name="category_id" 
                    id="category_id" 
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition"
                    required
                >
                    <option value="" class="bg-[#181716] text-stone-400">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} class="bg-[#181716] text-white">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Base Price -->
            <div class="space-y-1.5">
                <label for="price" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Harga Jual Standar (Rp) <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500 font-mono font-bold text-xs">Rp</span>
                    <input 
                        type="number" 
                        step="100" 
                        name="price" 
                        id="price" 
                        value="{{ old('price') }}" 
                        placeholder="25000"
                        class="w-full pl-10 pr-4 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white placeholder-stone-600 transition"
                        required
                    >
                </div>
                @error('price')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Description -->
            <div class="space-y-1.5">
                <label for="description" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Deskripsi Menu (Opsional)
                </label>
                <textarea 
                    name="description" 
                    id="description" 
                    rows="3" 
                    placeholder="Deskripsi bahan dasar atau catatan rasa..."
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                >{{ old('description') }}</textarea>
                @error('description')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Active Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', true) ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-300">Produk Langsung Aktif di Layar POS</span>
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
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Produk</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
