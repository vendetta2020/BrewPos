@extends('layouts.admin')

@section('title', 'Tambah Kategori — BrewPOS')
@section('page_title', 'Tambah Kategori Baru')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs p-6 space-y-6">
        <div class="border-b border-stone-200/80 pb-4">
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Kategori Menu Baru</h2>
            <p class="text-xs text-stone-500 mt-0.5">Kategori akan muncul sebagai tab navigasi cepat pada antarmuka kasir POS.</p>
        </div>

        <form action="{{ route('admin.categories.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Category Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                    Nama Kategori <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Signature Coffee"
                    class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-stone-900 placeholder-stone-400 transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Active Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', true) ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-700">Aktifkan Kategori di Layar POS</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-stone-200/80 flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.categories.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-semibold text-stone-600 hover:bg-stone-50 transition"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition flex items-center gap-2"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Kategori</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
