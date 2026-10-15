@extends('layouts.admin')

@section('title', 'Atur Resep: ' . $product->name . ' — BrewPOS')
@section('page_title', 'Atur Resep: ' . $product->name)

@section('content')
@php $recipeCount = max(count($recipes), 1); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
                <div class="border-b border-[#272320] pb-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-tight">{{ $product->name }}</h2>
                        <p class="text-xs text-[#A8A29E] mt-0.5">Harga Jual Menu: <strong class="font-mono text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</strong></p>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                        {{ $product->category?->name ?? 'Menu' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('admin.recipes.store', $product) }}" id="recipe-form" class="space-y-5">
                    @csrf

                    <!-- Recipe Items Container -->
                    <div id="recipes-container" class="space-y-4">
                        @forelse ($recipes as $index => $recipe)
                            <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3">
                                <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                                    <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                                        <span>Bahan Komposisi #{{ $index + 1 }}</span>
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
                                        <select name="recipes[{{ $index }}][raw_material_id]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                                            @foreach ($materials as $material)
                                                <option value="{{ $material->id }}" {{ $recipe->raw_material_id === $material->id ? 'selected' : '' }} class="bg-[#181716] text-white">
                                                    {{ $material->name }} ({{ $material->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Qty Used -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                                        <input type="number" name="recipes[{{ $index }}][qty_used]" value="{{ $recipe->qty_used }}" step="0.01" min="0.01" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" required>
                                    </div>

                                    <!-- Size Variant -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                                        <select name="recipes[{{ $index }}][size_variant]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="all" {{ $recipe->size_variant === 'all' ? 'selected' : '' }} class="bg-[#181716] text-white">Semua Ukuran (All)</option>
                                            <option value="S" {{ $recipe->size_variant === 'S' ? 'selected' : '' }} class="bg-[#181716] text-white">Kecil (S)</option>
                                            <option value="M" {{ $recipe->size_variant === 'M' ? 'selected' : '' }} class="bg-[#181716] text-white">Sedang (M)</option>
                                            <option value="L" {{ $recipe->size_variant === 'L' ? 'selected' : '' }} class="bg-[#181716] text-white">Besar (L)</option>
                                        </select>
                                    </div>

                                    <!-- Notes -->
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Catatan Takaran (Opsional)</label>
                                        <input type="text" name="recipes[{{ $index }}][notes]" value="{{ $recipe->notes }}" placeholder="Contoh: Double shot espresso" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 placeholder-stone-600">
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3">
                                <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                                    <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                                        <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                                        <span>Bahan Komposisi #1</span>
                                    </h3>
                                    <button type="button" class="text-rose-400 hover:text-rose-300 text-xs font-semibold remove-recipe flex items-center gap-1 transition">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Bahan Baku</label>
                                        <select name="recipes[0][raw_material_id]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                            <option value="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                                            @foreach ($materials as $material)
                                                <option value="{{ $material->id }}" class="bg-[#181716] text-white">{{ $material->name }} ({{ $material->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                                        <input type="number" name="recipes[0][qty_used]" value="0" step="0.01" min="0.01" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" required>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                                        <select name="recipes[0][size_variant]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
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
                    <button type="button" id="add-recipe-btn" data-recipe-count="{{ $recipeCount }}" class="w-full py-2.5 px-4 bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 text-xs font-bold uppercase tracking-wider rounded-xl border border-[#2D2A26] transition flex items-center justify-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4 text-amber-500"></i>
                        <span>Tambah Item Bahan Komposisi</span>
                    </button>

                    <!-- Submit Buttons -->
                    <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                        <a href="{{ route('admin.recipes.index') }}" class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition">
                            Batal
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2">
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
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Kalkulasi HPP Preview</h3>
                        <p class="text-[11px] text-[#A8A29E]">Harga Pokok Produksi bahan baku</p>
                    </div>
                </div>

                <div class="space-y-2.5">
                    @if (count($hpp) > 0)
                        @foreach ($hpp as $size => $cost)
                            <div class="bg-[#141210] border border-[#2D2A26] rounded-xl p-3 flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] font-bold text-[#A8A29E] uppercase tracking-wider block">Ukuran</span>
                                    <span class="text-xs font-bold text-white">{{ $size === 'all' ? 'Semua Ukuran' : 'Varian ' . $size }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-[#A8A29E] font-mono block">Estimasi Biaya</span>
                                    <span class="text-sm font-mono font-extrabold text-amber-400">
                                        Rp {{ number_format($cost, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 text-xs text-amber-400">
                            Belum ada item bahan. Tambahkan item resep untuk melihat estimasi HPP per ukuran.
                        </div>
                    @endif
                </div>

                <div class="p-3 bg-[#141210] border border-[#2D2A26] rounded-xl space-y-1 text-xs text-stone-300">
                    <div class="flex items-center gap-1.5 font-bold text-amber-500">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                        <span>Ketentuan HPP</span>
                    </div>
                    <p class="text-[11px] text-[#A8A29E] leading-relaxed">HPP dihitung otomatis dari jumlah bahan dikalikan harga perolehan bahan baku saat transaksi POS diproses.</p>
                </div>
            </div>
        </div>
    </div>

    @php $recipeCount = max(count($recipes), 1); @endphp
    @push('scripts')
    <script>
        let recipeCount = parseInt(document.getElementById('add-recipe-btn').dataset.recipeCount, 10) || 1;

        document.getElementById('add-recipe-btn').addEventListener('click', () => {
            const container = document.getElementById('recipes-container');
            const newRecipe = `
                <div class="recipe-item bg-[#141210] border border-[#2D2A26] rounded-xl p-4 space-y-3">
                    <div class="flex justify-between items-center pb-2 border-b border-[#272320]">
                        <h3 class="text-xs font-bold text-amber-500 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="beaker" class="w-3.5 h-3.5"></i>
                            <span>Bahan Komposisi #${recipeCount + 1}</span>
                        </h3>
                        <button type="button" class="text-rose-400 hover:text-rose-300 text-xs font-semibold remove-recipe flex items-center gap-1 transition">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Bahan Baku</label>
                            <select name="recipes[\${recipeCount}][raw_material_id]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                <option value="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                                @foreach ($materials as $material)
                                    <option value="{{ $material->id }}" class="bg-[#181716] text-white">{{ $material->name }} ({{ $material->unit }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Takaran / Jumlah</label>
                            <input type="number" name="recipes[\${recipeCount}][qty_used]" value="0" step="0.01" min="0.01" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-mono font-bold text-white focus:outline-none focus:border-amber-500" required>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Berlaku Untuk Ukuran</label>
                            <select name="recipes[\${recipeCount}][size_variant]" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs font-medium text-white focus:outline-none focus:border-amber-500" required>
                                <option value="all" class="bg-[#181716] text-white">Semua Ukuran (All)</option>
                                <option value="S" class="bg-[#181716] text-white">Kecil (S)</option>
                                <option value="M" class="bg-[#181716] text-white">Sedang (M)</option>
                                <option value="L" class="bg-[#181716] text-white">Besar (L)</option>
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-[11px] font-bold text-[#A8A29E] uppercase tracking-wider">Catatan Takaran (Opsional)</label>
                            <input type="text" name="recipes[\${recipeCount}][notes]" placeholder="Contoh: Suhu steamed 65°C" class="w-full px-3 py-2 bg-[#181716] border border-[#2D2A26] rounded-lg text-xs text-white focus:outline-none focus:border-amber-500 placeholder-stone-600">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newRecipe);
            recipeCount++;
            document.getElementById('add-recipe-btn').dataset.recipeCount = recipeCount;
            attachRemoveListeners();
            if (window.lucide) lucide.createIcons();
        });

        function attachRemoveListeners() {
            document.querySelectorAll('.remove-recipe').forEach(btn => {
                btn.removeEventListener('click', removeRecipe);
                btn.addEventListener('click', removeRecipe);
            });
        }

        function removeRecipe(e) {
            e.preventDefault();
            if (document.querySelectorAll('.recipe-item').length > 1) {
                e.target.closest('.recipe-item').remove();
            } else {
                alert('Resep harus memiliki minimal 1 item bahan!');
            }
        }

        attachRemoveListeners();
    </script>
    @endpush
@endsection
