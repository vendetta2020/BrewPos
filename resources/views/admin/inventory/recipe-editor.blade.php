@extends('layouts.admin')

@section('title', 'Atur Resep: ' . $product->name . ' — BrewPOS')
@section('page_title', 'Atur Resep: ' . $product->name)

@section('content')
@php $recipeCount = max(count($recipes), 1); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
                <div class="border-b border-[#272320] pb-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base font-bold text-white tracking-tight">{{ $product->name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                {{ $product->category?->name ?? 'Menu' }}
                            </span>
                        </div>
                        <p class="text-xs text-[#A8A29E] mt-0.5">
                            Harga Jual Menu: <strong class="font-mono text-white text-sm" id="product-price" data-price="{{ (float)$product->price }}">Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                        </p>
                    </div>

                    @if ($recipes->count() > 0)
                        <form action="{{ route('admin.recipes.destroy', $product) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seluruh resep untuk produk {{ $product->name }}? Tindakan ini tidak dapat dibatalkan.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 hover:text-rose-200 text-xs font-semibold flex items-center gap-1.5 transition">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Hapus Seluruh Resep</span>
                            </button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.recipes.store') }}" id="recipe-form" class="space-y-5">
                    @csrf
                    <!-- Hidden Product ID Field (Crucial for validation) -->
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <!-- Recipe Items Container -->
                    <div id="recipes-container" class="space-y-4">
                        @forelse ($recipes as $index => $recipe)
                            <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3" data-row="{{ $index }}">
                                <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                                    <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                                        <span class="recipe-item-title">Bahan Komposisi #{{ $index + 1 }}</span>
                                    </h3>
                                    <button type="button" class="text-rose-400 hover:text-rose-300 text-xs font-semibold remove-recipe flex items-center gap-1 transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Bahan Baku -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Bahan Baku</label>
                                        <select name="recipes[{{ $index }}][raw_material_id]" class="raw-material-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="" data-cost="0" data-unit="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                                            @foreach ($materials as $material)
                                                <option 
                                                    value="{{ $material->id }}" 
                                                    data-cost="{{ (float)$material->cost_per_unit }}"
                                                    data-unit="{{ $material->unit }}"
                                                    {{ $recipe->raw_material_id === $material->id ? 'selected' : '' }} 
                                                    class="bg-[#181716] text-white"
                                                >
                                                    {{ $material->name }} ({{ $material->unit }}) — Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}/{{ $material->unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Qty Used -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between items-center">
                                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                                            <span class="unit-label text-[10px] font-mono text-amber-400 font-bold">
                                                {{ $recipe->rawMaterial?->unit ?? '' }}
                                            </span>
                                        </div>
                                        <input type="number" name="recipes[{{ $index }}][qty_used]" value="{{ $recipe->qty_used }}" step="0.01" min="0.01" class="qty-input w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" placeholder="0.00" required>
                                    </div>

                                    <!-- Size Variant -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                                        <select name="recipes[{{ $index }}][size_variant]" class="size-variant-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="all" {{ $recipe->size_variant === 'all' ? 'selected' : '' }} class="bg-[#181716] text-white">Semua Ukuran (All)</option>
                                            <option value="S" {{ $recipe->size_variant === 'S' ? 'selected' : '' }} class="bg-[#181716] text-white">Kecil (S)</option>
                                            <option value="M" {{ $recipe->size_variant === 'M' ? 'selected' : '' }} class="bg-[#181716] text-white">Sedang (M)</option>
                                            <option value="L" {{ $recipe->size_variant === 'L' ? 'selected' : '' }} class="bg-[#181716] text-white">Besar (L)</option>
                                        </select>
                                    </div>

                                    <!-- Notes -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Catatan Takaran (Opsional)</label>
                                        <input type="text" name="recipes[{{ $index }}][notes]" value="{{ $recipe->notes }}" placeholder="Contoh: Double shot espresso / 60ml" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 placeholder-stone-600">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3" data-row="0">
                                <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                                    <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                                        <span class="recipe-item-title">Bahan Komposisi #1</span>
                                    </h3>
                                    <button type="button" class="text-rose-400 hover:text-rose-300 text-xs font-semibold remove-recipe flex items-center gap-1 transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Bahan Baku</label>
                                        <select name="recipes[0][raw_material_id]" class="raw-material-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="" data-cost="0" data-unit="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                                            @foreach ($materials as $material)
                                                <option 
                                                    value="{{ $material->id }}" 
                                                    data-cost="{{ (float)$material->cost_per_unit }}"
                                                    data-unit="{{ $material->unit }}"
                                                    class="bg-[#181716] text-white"
                                                >
                                                    {{ $material->name }} ({{ $material->unit }}) — Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}/{{ $material->unit }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <div class="flex justify-between items-center">
                                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                                            <span class="unit-label text-[10px] font-mono text-amber-400 font-bold"></span>
                                        </div>
                                        <input type="number" name="recipes[0][qty_used]" value="" step="0.01" min="0.01" class="qty-input w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" placeholder="0.00" required>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                                        <select name="recipes[0][size_variant]" class="size-variant-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="all" selected class="bg-[#181716] text-white">Semua Ukuran (All)</option>
                                            <option value="S" class="bg-[#181716] text-white">Kecil (S)</option>
                                            <option value="M" class="bg-[#181716] text-white">Sedang (M)</option>
                                            <option value="L" class="bg-[#181716] text-white">Besar (L)</option>
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Catatan Takaran (Opsional)</label>
                                        <input type="text" name="recipes[0][notes]" placeholder="Contoh: Suhu steamed 65°C" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 placeholder-stone-600">
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add More Button -->
                    <button type="button" id="add-recipe-btn" class="w-full py-3 px-4 bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 hover:text-white text-xs font-bold uppercase tracking-wider rounded-xl border border-[#2D2A26] transition flex items-center justify-center gap-2 cursor-pointer shadow-sm">
                        <i data-lucide="plus" class="w-4 h-4 text-amber-500"></i>
                        <span>Tambah Item Bahan Komposisi</span>
                    </button>

                    <!-- Submit Buttons -->
                    <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                        <a href="{{ route('admin.recipes.index') }}" class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition">
                            Batal
                        </a>
                        <button type="submit" id="save-recipe-btn" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2 cursor-pointer">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Resep Komposisi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- HPP Preview Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-5 sticky top-6 space-y-4">
                <div class="flex items-center gap-2 pb-3 border-b border-[#272320]">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="calculator" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Kalkulasi HPP Real-Time</h3>
                        <p class="text-[11px] text-[#A8A29E]">Estimasi Biaya Pokok Produksi</p>
                    </div>
                </div>

                <div id="hpp-cards-container" class="space-y-2.5">
                    @if (count($hpp) > 0)
                        @foreach ($hpp as $size => $cost)
                            @php
                                $price = (float)$product->price;
                                $margin = $price > 0 ? max(0, round((($price - $cost) / $price) * 100, 1)) : 0;
                            @endphp
                            <div class="bg-[#141210] border border-[#2D2A26] rounded-xl p-3 flex justify-between items-center" data-size="{{ $size }}">
                                <div>
                                    <span class="text-[10px] font-bold text-[#A8A29E] uppercase tracking-wider block">Ukuran</span>
                                    <span class="text-xs font-bold text-white size-label">{{ $size === 'all' ? 'Semua Ukuran (All)' : 'Varian ' . $size }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-[#A8A29E] font-mono block">Estimasi Biaya</span>
                                    <span class="text-sm font-mono font-extrabold text-amber-400 cost-val">
                                        Rp {{ number_format($cost, 0, ',', '.') }}
                                    </span>
                                    @if ($price > 0)
                                        <span class="text-[9px] font-mono block text-emerald-400 margin-val">
                                            Margin ~{{ $margin }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div id="empty-hpp-msg" class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 text-xs text-amber-400">
                            Pilih bahan dan takaran untuk melihat kalkulasi HPP real-time.
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-[#141210] border border-[#2D2A26] rounded-xl space-y-1 text-xs text-stone-300">
                    <div class="flex items-center gap-1.5 font-bold text-amber-500">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>Ketentuan Auto-Deduction & HPP</span>
                    </div>
                    <p class="text-[11px] text-[#A8A29E] leading-relaxed">
                        Takaran bahan akan otomatis memotong stok gudang saat transaksi dibayar di kasir. HPP dihitung real-time berdasarkan harga perolehan bahan baku terkini.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Template for Dynamic Row -->
    <template id="recipe-row-template">
        <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3" data-row="__INDEX__">
            <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                    <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                    <span class="recipe-item-title">Bahan Komposisi #__NUM__</span>
                </h3>
                <button type="button" class="text-rose-400 hover:text-rose-300 text-xs font-semibold remove-recipe flex items-center gap-1 transition">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    <span>Hapus</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Bahan Baku</label>
                    <select name="recipes[__INDEX__][raw_material_id]" class="raw-material-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                        <option value="" data-cost="0" data-unit="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                        @foreach ($materials as $material)
                            <option 
                                value="{{ $material->id }}" 
                                data-cost="{{ (float)$material->cost_per_unit }}"
                                data-unit="{{ $material->unit }}"
                                class="bg-[#181716] text-white"
                            >
                                {{ $material->name }} ({{ $material->unit }}) — Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}/{{ $material->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <div class="flex justify-between items-center">
                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                        <span class="unit-label text-[10px] font-mono text-amber-400 font-bold"></span>
                    </div>
                    <input type="number" name="recipes[__INDEX__][qty_used]" value="" step="0.01" min="0.01" class="qty-input w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" placeholder="0.00" required>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                    <select name="recipes[__INDEX__][size_variant]" class="size-variant-select w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                        <option value="all" selected class="bg-[#181716] text-white">Semua Ukuran (All)</option>
                        <option value="S" class="bg-[#181716] text-white">Kecil (S)</option>
                        <option value="M" class="bg-[#181716] text-white">Sedang (M)</option>
                        <option value="L" class="bg-[#181716] text-white">Besar (L)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Catatan Takaran (Opsional)</label>
                    <input type="text" name="recipes[__INDEX__][notes]" placeholder="Contoh: Suhu steamed 65°C" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 placeholder-stone-600">
                </div>
            </div>
        </div>
    </template>

    @push('scripts')
    <script>
        let nextIndex = {{ max(count($recipes), 1) }};

        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(number);
        }

        function updateItemBadges() {
            const items = document.querySelectorAll('.recipe-item');
            items.forEach((item, idx) => {
                const title = item.querySelector('.recipe-item-title');
                if (title) {
                    title.textContent = `Bahan Komposisi #${idx + 1}`;
                }
            });
        }

        function updateUnitLabel(row) {
            const select = row.querySelector('.raw-material-select');
            const unitLabel = row.querySelector('.unit-label');
            if (select && unitLabel) {
                const selectedOption = select.options[select.selectedIndex];
                const unit = selectedOption ? selectedOption.dataset.unit : '';
                unitLabel.textContent = unit ? `(${unit})` : '';
            }
        }

        function calculateLiveHpp() {
            const productPrice = parseFloat(document.getElementById('product-price')?.dataset.price || 0);
            const container = document.getElementById('hpp-cards-container');
            const emptyMsg = document.getElementById('empty-hpp-msg');
            const rows = document.querySelectorAll('.recipe-item');

            const sizesPresent = new Set();
            rows.forEach(row => {
                const size = row.querySelector('.size-variant-select')?.value || 'all';
                sizesPresent.add(size);
            });

            // Target sizes to display: if only 'all', display 'all'. If specific sizes present, display each
            const targetSizes = sizesPresent.has('all') && sizesPresent.size === 1 ? ['all'] : Array.from(sizesPresent);
            if (!targetSizes.includes('all') && sizesPresent.size > 0) {
                targetSizes.unshift('all');
            }

            let cardsHtml = '';
            let hasAnyValidIngredient = false;

            targetSizes.forEach(size => {
                let cost = 0;
                rows.forEach(row => {
                    const select = row.querySelector('.raw-material-select');
                    const qtyInput = row.querySelector('.qty-input');
                    const rowSize = row.querySelector('.size-variant-select')?.value || 'all';

                    if (select && qtyInput && select.value) {
                        const unitCost = parseFloat(select.options[select.selectedIndex]?.dataset.cost || 0);
                        const qty = parseFloat(qtyInput.value || 0);

                        if (qty > 0) {
                            hasAnyValidIngredient = true;
                            if (size === 'all' || rowSize === 'all' || rowSize === size) {
                                cost += unitCost * qty;
                            }
                        }
                    }
                });

                if (cost > 0 || hasAnyValidIngredient) {
                    const margin = productPrice > 0 ? Math.max(0, Math.round(((productPrice - cost) / productPrice) * 1000) / 10) : 0;
                    const sizeName = size === 'all' ? 'Semua Ukuran (All)' : `Varian ${size}`;
                    cardsHtml += `
                        <div class="bg-[#141210] border border-[#2D2A26] rounded-xl p-3 flex justify-between items-center transition" data-size="${size}">
                            <div>
                                <span class="text-[10px] font-bold text-[#A8A29E] uppercase tracking-wider block">Ukuran</span>
                                <span class="text-xs font-bold text-white size-label">${sizeName}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#A8A29E] font-mono block">Estimasi Biaya</span>
                                <span class="text-sm font-mono font-extrabold text-amber-400 cost-val">
                                    ${formatRupiah(cost)}
                                </span>
                                ${productPrice > 0 ? `<span class="text-[9px] font-mono block text-emerald-400 margin-val">Margin ~${margin}%</span>` : ''}
                            </div>
                        </div>
                    `;
                }
            });

            if (cardsHtml) {
                container.innerHTML = cardsHtml;
            } else if (emptyMsg) {
                container.innerHTML = `
                    <div id="empty-hpp-msg" class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 text-xs text-amber-400">
                        Pilih bahan dan takaran untuk melihat kalkulasi HPP real-time.
                    </div>
                `;
            }
        }

        // Add Row Handler
        document.getElementById('add-recipe-btn').addEventListener('click', () => {
            const template = document.getElementById('recipe-row-template').innerHTML;
            const currentRows = document.querySelectorAll('.recipe-item').length;
            const newHtml = template
                .replace(/__INDEX__/g, nextIndex)
                .replace(/__NUM__/g, currentRows + 1);

            document.getElementById('recipes-container').insertAdjacentHTML('beforeend', newHtml);
            nextIndex++;

            updateItemBadges();
            if (window.lucide) {
                lucide.createIcons();
            }
            calculateLiveHpp();
        });

        // Event delegation for Remove Row
        document.getElementById('recipes-container').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-recipe');
            if (removeBtn) {
                e.preventDefault();
                const rows = document.querySelectorAll('.recipe-item');
                if (rows.length > 1) {
                    removeBtn.closest('.recipe-item').remove();
                    updateItemBadges();
                    calculateLiveHpp();
                } else {
                    alert('Resep harus memiliki minimal 1 item bahan!');
                }
            }
        });

        // Event delegation for Changes (Unit label & HPP)
        document.getElementById('recipes-container').addEventListener('change', (e) => {
            const row = e.target.closest('.recipe-item');
            if (row) {
                updateUnitLabel(row);
                calculateLiveHpp();
            }
        });

        document.getElementById('recipes-container').addEventListener('input', (e) => {
            if (e.target.classList.contains('qty-input')) {
                calculateLiveHpp();
            }
        });

        // Initialize unit labels on existing rows
        document.querySelectorAll('.recipe-item').forEach(row => {
            updateUnitLabel(row);
        });

        // Initialize live HPP calculation on page load
        calculateLiveHpp();
    </script>
    @endpush
@endsection
