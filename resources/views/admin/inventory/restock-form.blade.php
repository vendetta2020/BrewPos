@extends('layouts.admin')

@section('title', 'Catat Restock — BrewPOS')
@section('page_title', 'Catat Masuk Bahan Baku')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">Pencatatan Masuk Bahan Baku (Restock)</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Catat penerimaan bahan baku gudang. Kuantitas fisik akan bertambah otomatis dan dicatat dalam audit log mutasi (tipe: IN).</p>
        </div>

        <form method="POST" action="{{ route('admin.restocks.store') }}" class="space-y-5">
            @csrf

            <!-- Alert Message -->
            @if (session('success'))
                <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2.5">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Bahan Baku Selection -->
            <div class="space-y-1.5">
                <label for="raw_material_id" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Bahan Baku <span class="text-rose-400">*</span>
                </label>
                <select 
                    id="raw_material_id"
                    name="raw_material_id"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition"
                    required
                >
                    <option value="" class="bg-[#181716] text-stone-400">-- Pilih Bahan Baku --</option>
                    @forelse ($materials as $material)
                        <option value="{{ $material->id }}" {{ old('raw_material_id') == $material->id ? 'selected' : '' }} class="bg-[#181716] text-white">
                            {{ $material->name }} ({{ $material->unit }}) — Stok Fisik: {{ number_format($material->stock_qty, 2, ',', '.') }}
                        </option>
                    @empty
                        <option disabled class="bg-[#181716] text-stone-500">Tidak ada bahan baku. Tambahkan bahan baku baru terlebih dahulu.</option>
                    @endforelse
                </select>
                @error('raw_material_id')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Jumlah Masuk & Harga per Unit -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="qty_in" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Jumlah Kuantitas Masuk <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="qty_in"
                        name="qty_in"
                        value="{{ old('qty_in') }}"
                        placeholder="Contoh: 5000"
                        step="0.01"
                        min="0.01"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                        required
                    >
                    @error('qty_in')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="cost_per_unit" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Harga Beli / Unit (Rp) <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-500 font-mono font-bold text-xs">Rp</span>
                        <input 
                            type="number" 
                            id="cost_per_unit"
                            name="cost_per_unit"
                            value="{{ old('cost_per_unit') }}"
                            placeholder="0"
                            step="0.01"
                            min="0"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-bold text-white transition"
                            required
                        >
                    </div>
                    @error('cost_per_unit')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Supplier -->
            <div class="space-y-1.5">
                <label for="supplier" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Supplier / Sumber Pengadaan (Opsional)
                </label>
                <input 
                    type="text" 
                    id="supplier"
                    name="supplier"
                    value="{{ old('supplier') }}"
                    placeholder="Contoh: CV Biji Kopi Nusantara, PT Susu Segar"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                >
                @error('supplier')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Catatan Tambahan -->
            <div class="space-y-1.5">
                <label for="note" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea 
                    id="note"
                    name="note"
                    rows="2"
                    placeholder="Contoh: Pengiriman batch mingguan, kemasan utuh, kedaluwarsa 2027"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                >{{ old('note') }}</textarea>
                @error('note')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Info Box -->
            <div class="p-4 bg-[#141210] border border-[#2D2A26] rounded-xl space-y-2 text-xs text-stone-300">
                <div class="flex items-center gap-1.5 font-bold text-amber-500">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <span>Informasi Mutasi Stok</span>
                </div>
                <ul class="text-[11px] text-[#A8A29E] space-y-1 pl-5 list-disc">
                    <li>Kuantitas stok bahan akan otomatis bertambah secara atomik.</li>
                    <li>Harga perolehan baru akan menjadi basis perhitungan HPP produk resep.</li>
                    <li>Seluruh transaksi restock tersimpan permanen di Log Mutasi Stok.</li>
                </ul>
            </div>

            <!-- Buttons -->
            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.raw-materials.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition"
                >
                    Kembali
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2"
                >
                    <i data-lucide="arrow-down-to-dot" class="w-4 h-4"></i>
                    <span>Catat Restock Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
