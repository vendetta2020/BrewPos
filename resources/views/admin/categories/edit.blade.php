@extends('layouts.admin')

@section('title', 'Edit Kategori — BrewPOS')
@section('page_title', 'Edit Kategori')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">Perbarui Kategori #{{ $category->id }}</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Ubah nama kategori atau status visibilitas di kasir POS.</p>
        </div>

        <form action="{{ route('admin.categories.update', $category) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Category Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Nama Kategori <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name', $category->name) }}" 
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Active Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $category->is_active) ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-300">Aktifkan Kategori di Layar POS</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.categories.index') }}" 
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
