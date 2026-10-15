@extends('layouts.admin')

@section('title', 'Edit Bahan Baku - BrewPos')
@section('page_title', 'Edit Bahan Baku')

@section('content')
<div class="w-full max-w-[560px] mx-auto flex flex-col gap-6">
    <!-- Form Card -->
    <section class="bg-white rounded-xl border-[0.5px] border-outline-variant p-8 transition-all">
        <form action="{{ route('admin.raw-materials.update', $material) }}" method="POST" class="grid grid-cols-2 gap-x-6 gap-y-6" id="ingredientForm">
            @csrf
            @method('PUT')

            <!-- Nama Bahan (Full Width) -->
            <div class="col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="name">Nama Bahan</label>
                <input 
                    class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all" 
                    id="name" 
                    name="name"
                    value="{{ old('name', $material->name) }}"
                    placeholder="Contoh: Espresso Blend House" 
                    type="text"
                    required
                />
                @error('name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Kategori -->
            <div class="col-span-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="category">Kategori</label>
                <select 
                    class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all appearance-none cursor-pointer" 
                    id="category"
                    name="category"
                    required
                >
                    <option value="">Pilih Kategori</option>
                    <option value="Kopi" {{ old('category', $material->category) === 'Kopi' ? 'selected' : '' }}>Biji Kopi</option>
                    <option value="Susu" {{ old('category', $material->category) === 'Susu' ? 'selected' : '' }}>Susu &amp; Dairy</option>
                    <option value="Sirup" {{ old('category', $material->category) === 'Sirup' ? 'selected' : '' }}>Sirup &amp; Pemanis</option>
                    <option value="Bubuk" {{ old('category', $material->category) === 'Bubuk' ? 'selected' : '' }}>Bubuk Minuman</option>
                    <option value="Packaging" {{ old('category', $material->category) === 'Packaging' ? 'selected' : '' }}>Kemasan</option>
                </select>
                @error('category')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Satuan -->
            <div class="col-span-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="unit">Satuan</label>
                <select 
                    class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all appearance-none cursor-pointer" 
                    id="unit"
                    name="unit"
                    required
                >
                    <option value="">Pilih Satuan</option>
                    <option value="gram" {{ old('unit', $material->unit) === 'gram' ? 'selected' : '' }}>Gram (g)</option>
                    <option value="ml" {{ old('unit', $material->unit) === 'ml' ? 'selected' : '' }}>Mililiter (ml)</option>
                    <option value="pcs" {{ old('unit', $material->unit) === 'pcs' ? 'selected' : '' }}>Pcs</option>
                    <option value="liter" {{ old('unit', $material->unit) === 'liter' ? 'selected' : '' }}>Liter (L)</option>
                </select>
                @error('unit')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Harga Beli per Unit -->
            <div class="col-span-2">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="cost_per_unit">Harga Beli per Unit</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-body-md text-on-surface-variant">Rp</span>
                    <input 
                        class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg pl-12 pr-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all" 
                        id="cost_per_unit" 
                        name="cost_per_unit"
                        value="{{ old('cost_per_unit', $material->cost_per_unit) }}"
                        placeholder="0" 
                        type="number"
                        step="0.01"
                        required
                    />
                </div>
                <p class="mt-1 font-label-sm text-label-sm text-on-surface-variant italic">misal: 200 untuk Rp 200/gram</p>
                @error('cost_per_unit')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Stok Awal -->
            <div class="col-span-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="stock_qty">Stok Saat Ini</label>
                <input 
                    class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all" 
                    id="stock_qty" 
                    name="stock_qty"
                    value="{{ old('stock_qty', $material->stock_qty) }}"
                    placeholder="0" 
                    type="number"
                    step="0.01"
                    required
                />
                @error('stock_qty')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Minimum Stok -->
            <div class="col-span-1">
                <label class="block font-label-md text-label-md text-on-surface-variant mb-2" for="min_stock_qty">Minimum Stok</label>
                <input 
                    class="w-full bg-surface border-[0.5px] border-outline-variant rounded-lg px-4 py-2.5 font-body-md text-body-md focus:ring-1 focus:ring-brand focus:border-brand outline-none transition-all" 
                    id="min_stock_qty" 
                    name="min_stock_qty"
                    value="{{ old('min_stock_qty', $material->min_stock_qty) }}"
                    placeholder="0" 
                    type="number"
                    step="0.01"
                    required
                />
                <p class="mt-1 font-label-sm text-label-sm text-on-surface-variant">Alert saat stok menipis</p>
                @error('min_stock_qty')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </form>
    </section>

    <!-- Live Preview Card -->
    <div class="bg-brand-light rounded-xl border-[0.5px] border-brand px-5 py-5 flex items-center justify-between transition-all duration-300" id="previewCard">
        <div class="flex items-center gap-3">
            <div class="bg-white p-2 rounded-full border border-outline-variant">
                <span class="material-symbols-outlined text-brand text-[20px]">payments</span>
            </div>
            <span class="font-body-md text-body-md text-on-surface">Estimasi nilai stok saat ini:</span>
        </div>
        <div class="text-right">
            <span class="font-headline-md text-headline-md text-brand" id="estimatedValue">Rp 0</span>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-end gap-2 mb-6">
        <a href="{{ route('admin.raw-materials.index') }}" class="px-6 py-2.5 border-[0.5px] border-outline-variant rounded-lg text-on-surface-variant font-label-md text-label-md bg-white hover:bg-surface-container transition-colors active:opacity-80">
            Batal
        </a>
        <button 
            type="submit"
            form="ingredientForm"
            class="px-8 py-2.5 bg-brand text-white rounded-lg font-label-md text-label-md flex items-center gap-2 hover:opacity-95 active:scale-95 transition-all"
            id="submitBtn"
        >
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            Simpan Perubahan
        </button>
    </div>
</div>

<script>
    // Micro-interaction: Live estimation calculation
    const costInput = document.getElementById('cost_per_unit');
    const stockInput = document.getElementById('stock_qty');
    const displayValue = document.getElementById('estimatedValue');
    const previewCard = document.getElementById('previewCard');
    const submitBtn = document.getElementById('submitBtn');

    function updateEstimation() {
        const cost = parseFloat(costInput.value) || 0;
        const stock = parseFloat(stockInput.value) || 0;
        const total = cost * stock;
        
        // Format to Indonesian Rupiah
        const formatter = new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        });

        displayValue.textContent = formatter.format(total);

        // Subtle highlight on update
        if (total > 0) {
            previewCard.classList.remove('opacity-70');
            previewCard.classList.add('ring-1', 'ring-brand');
            setTimeout(() => {
                previewCard.classList.remove('ring-1', 'ring-brand');
            }, 400);
        } else {
            previewCard.classList.add('opacity-70');
        }
    }

    costInput.addEventListener('input', updateEstimation);
    stockInput.addEventListener('input', updateEstimation);

    // Initial state
    updateEstimation();

    // Form submission effect (optional loading animation)
    document.getElementById('ingredientForm').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50');
    });
</script>
@endsection
