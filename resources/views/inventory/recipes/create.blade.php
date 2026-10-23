@extends('layouts.admin')

@section('title', 'Buat Resep Menu Baru — BrewPOS')
@section('page_title', 'Formulasi Resep Menu Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 sm:p-8 space-y-6">
        <div class="border-b border-[#272320] pb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                <i data-lucide="plus-circle" class="w-5 h-5"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-white tracking-tight">Pilih Menu Produk</h2>
                <p class="text-xs text-[#A8A29E] mt-0.5">Pilih produk menu kafe yang akan dibuatkan atau disesuaikan formulasi resep takarannya.</p>
            </div>
        </div>

        <form id="select-product-form" class="space-y-6" onsubmit="event.preventDefault(); goToRecipe();">
            <div class="space-y-2">
                <label for="product_select" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Daftar Menu Tersedia <span class="text-amber-500">*</span>
                </label>
                @php
                    $productOptions = $products->mapWithKeys(function ($p) {
                        $recipeTag = $p->recipes()->exists() ? '(Sudah Ada Resep)' : '(Belum Ada Resep)';
                        return [$p->id => "{$p->name} — Rp " . number_format($p->price, 0, ',', '.') . " {$recipeTag}"];
                    })->toArray();
                @endphp
                <x-dropdown-select 
                    id="product_select"
                    name="product_id"
                    :options="$productOptions"
                    placeholder="-- Pilih Produk Menu --"
                    required
                />
                <p class="text-[11px] text-[#A8A29E]">
                    Setelah memilih produk, Anda akan diarahkan ke Recipe Editor untuk menentukan bahan baku, gramasi takaran, dan preview HPP secara real-time.
                </p>
            </div>

            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a href="{{ route('admin.recipes.index') }}" class="px-5 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition">
                    Kembali
                </a>
                <button 
                    type="submit" 
                    id="submit-btn" 
                    class="px-6 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2 cursor-pointer"
                >
                    <span>Lanjut Atur Komposisi</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function goToRecipe() {
        const select = document.getElementById('product_select');
        const productId = select.value;
        if (!productId) {
            alert('Silakan pilih produk menu terlebih dahulu.');
            return;
        }
        window.location.href = `/admin/recipes/${productId}/edit`;
    }
</script>
@endpush
@endsection
