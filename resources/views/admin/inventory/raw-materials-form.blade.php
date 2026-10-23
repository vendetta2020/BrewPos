@extends('layouts.admin')

@section('title', (isset($material) ? 'Edit' : 'Tambah') . ' Bahan Baku — BrewPOS')
@section('page_title', (isset($material) ? 'Edit' : 'Tambah') . ' Bahan Baku')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">
                {{ isset($material) ? 'Perbarui Bahan Baku #' . $material->id : 'Tambah Bahan Baku Baru' }}
            </h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">
                {{ isset($material) ? 'Perbarui informasi satuan, harga pokok, atau stok minimum bahan baku' : 'Masukkan detail bahan mentah, takaran satuan, dan batas restock gudang' }}
            </p>
        </div>

        <form method="POST" action="{{ isset($material) ? route('admin.raw-materials.update', $material) : route('admin.raw-materials.store') }}" class="space-y-5">
            @csrf
            @if (isset($material))
                @method('PUT')
            @endif

            <!-- Nama -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Nama Bahan Baku <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="text" 
                    id="name"
                    name="name" 
                    value="{{ old('name', $material->name ?? '') }}"
                    placeholder="Contoh: Biji Kopi Arabika House Blend"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Kategori & Satuan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="category" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Kategori <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="category"
                        name="category" 
                        value="{{ old('category', $material->category ?? '') }}"
                        placeholder="Contoh: Kopi, Susu, Sirup"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                        required
                    >
                    @error('category')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="unit" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Satuan Pengukuran <span class="text-rose-400">*</span>
                    </label>
                    <x-dropdown-select 
                        id="unit"
                        name="unit" 
                        :options="[
                            'gram' => 'Gram (g)',
                            'ml' => 'Mililiter (ml)',
                            'pcs' => 'Pcs (pieces)',
                            'liter' => 'Liter (L)',
                        ]"
                        :selected="old('unit', $material->unit ?? '')"
                        placeholder="-- Pilih Satuan --"
                        required
                    />
                    @error('unit')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Harga & Stok Awal -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="space-y-1.5">
                    <label for="cost_per_unit" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Biaya / Unit (Rp) <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500 font-mono font-bold text-xs">Rp</span>
                        <input 
                            type="number" 
                            id="cost_per_unit"
                            name="cost_per_unit" 
                            value="{{ old('cost_per_unit', $material->cost_per_unit ?? '') }}"
                            placeholder="0"
                            step="0.01"
                            min="0"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                            required
                        >
                    </div>
                    <p class="text-[10px] text-stone-400 mt-1">Harga modal per 1 satuan (cth: Rp/gram atau Rp/ml).</p>
                    @error('cost_per_unit')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="stock_qty" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Stok Fisik <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="stock_qty"
                        name="stock_qty" 
                        value="{{ old('stock_qty', $material->stock_qty ?? '0') }}"
                        placeholder="0"
                        step="0.01"
                        min="0"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                        required
                    >
                    <p class="text-[10px] text-stone-400 mt-1">Sesuai satuan di atas (cth: gram/ml/pcs).</p>
                    @error('stock_qty')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="min_stock_qty" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Stok Minimum <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="min_stock_qty"
                        name="min_stock_qty" 
                        value="{{ old('min_stock_qty', $material->min_stock_qty ?? '0') }}"
                        placeholder="0"
                        step="0.01"
                        min="0"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                        required
                    >
                    <p class="text-[10px] text-stone-400 mt-1">Batas alert (cth: 1000 gram / 50 pcs).</p>
                    @error('min_stock_qty')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="text-[11px] text-[#A8A29E]">Peringatan stok kritis akan otomatis muncul jika stok fisik kurang dari atau sama dengan stok minimum.</p>

            <!-- Status Aktif -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        id="is_active" 
                        name="is_active" 
                        value="1"
                        {{ old('is_active', $material->is_active ?? true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-300">
                        Bahan Aktif (Dapat dipilih dan digunakan dalam formulasi resep menu)
                    </span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.raw-materials.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2"
                >
                    <i data-lucide="{{ isset($material) ? 'save' : 'plus' }}" class="w-4 h-4"></i>
                    <span>{{ isset($material) ? 'Simpan Perubahan' : 'Tambah Bahan Baku' }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
