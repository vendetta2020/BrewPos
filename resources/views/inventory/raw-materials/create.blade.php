@extends('layouts.admin')

@section('title', 'Tambah Bahan Baku - BrewPos')
@section('page_title', 'Tambah Bahan Baku')

@section('content')
    <div class="bg-white rounded-xl border-[0.5px] border-outline-variant p-6 max-w-2xl">
        <form action="{{ route('admin.raw-materials.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block font-body-md text-body-md text-on-surface mb-2">Nama Bahan</label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors"
                    required
                >
                @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="category" class="block font-body-md text-body-md text-on-surface mb-2">Kategori</label>
                <input 
                    type="text" 
                    name="category" 
                    id="category" 
                    value="{{ old('category') }}" 
                    class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors"
                    placeholder="Kopi, Susu, Sirup, Packaging, dll"
                    required
                >
                @error('category')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="unit" class="block font-body-md text-body-md text-on-surface mb-2">Satuan</label>
                    <select 
                        name="unit" 
                        id="unit" 
                        class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors appearance-none cursor-pointer"
                        required
                    >
                        <option value="">Pilih satuan</option>
                        <option value="gram" {{ old('unit') === 'gram' ? 'selected' : '' }}>Gram (gr)</option>
                        <option value="ml" {{ old('unit') === 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                        <option value="pcs" {{ old('unit') === 'pcs' ? 'selected' : '' }}>Piece (pcs)</option>
                        <option value="liter" {{ old('unit') === 'liter' ? 'selected' : '' }}>Liter (L)</option>
                    </select>
                    @error('unit')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="cost_per_unit" class="block font-body-md text-body-md text-on-surface mb-2">Harga/Unit (Rp)</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="cost_per_unit" 
                        id="cost_per_unit" 
                        value="{{ old('cost_per_unit') }}" 
                        class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors"
                        required
                    >
                    @error('cost_per_unit')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="stock_qty" class="block font-body-md text-body-md text-on-surface mb-2">Stok Saat Ini</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="stock_qty" 
                        id="stock_qty" 
                        value="{{ old('stock_qty') }}" 
                        class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors"
                        required
                    >
                    @error('stock_qty')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="min_stock_qty" class="block font-body-md text-body-md text-on-surface mb-2">Minimum Stok</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="min_stock_qty" 
                        id="min_stock_qty" 
                        value="{{ old('min_stock_qty') }}" 
                        class="w-full bg-surface-container-low border-[0.5px] border-outline-variant rounded-lg px-4 py-3 font-body-md text-body-md focus:outline-none focus:border-brand transition-colors"
                        required
                    >
                    @error('min_stock_qty')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input 
                    type="checkbox" 
                    name="is_active" 
                    id="is_active" 
                    value="1" 
                    {{ old('is_active', true) ? 'checked' : '' }}
                    class="w-4 h-4 border-[0.5px] border-outline-variant rounded cursor-pointer accent-brand"
                >
                <label for="is_active" class="font-body-md text-body-md text-on-surface cursor-pointer">Aktif</label>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t-[0.5px] border-outline-variant">
                <a href="{{ route('admin.raw-materials.index') }}" class="px-4 py-2.5 rounded-lg border-[0.5px] border-outline-variant font-body-md text-body-md text-secondary hover:bg-surface-container transition-colors">
                    Batal
                </a>
                <button 
                    type="submit"
                    class="px-6 py-2.5 rounded-lg bg-brand text-white font-body-md text-body-md font-semibold hover:opacity-90 active:opacity-80 transition-all"
                >
                    Simpan Bahan Baku
                </button>
            </div>
        </form>
    </div>
@endsection
