@extends('layouts.admin')

@section('title', 'Tambah Pengguna — BrewPOS')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs p-6 space-y-6">
        <div class="border-b border-stone-200/80 pb-4">
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Informasi Akun Karyawan</h2>
            <p class="text-xs text-stone-500 mt-0.5">Buat kredensial login untuk administrator, kasir (POS), atau barista (KDS).</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                    Nama Lengkap <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-stone-900 placeholder-stone-400 transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                    Email Akun <span class="text-rose-500">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="{{ old('email') }}" 
                    placeholder="nama@brewpos.test"
                    class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-medium text-stone-900 placeholder-stone-400 transition"
                    required
                >
                @error('email')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Role -->
            <div class="space-y-1.5">
                <label for="role" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                    Peran / Hak Akses (Role) <span class="text-rose-500">*</span>
                </label>
                <select 
                    name="role" 
                    id="role" 
                    class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-stone-900 transition"
                    required
                >
                    <option value="">-- Pilih Peran --</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin (Akses Penuh Manajemen)</option>
                    <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Kasir (Layar Transaksi POS)</option>
                    <option value="barista" {{ old('role') === 'barista' ? 'selected' : '' }}>Barista (Layar Dapur KDS)</option>
                </select>
                @error('role')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-stone-900 transition"
                        required
                    >
                    @error('password')<p class="text-xs text-rose-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-stone-700 uppercase tracking-wider">
                        Konfirmasi Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        placeholder="Ulangi kata sandi"
                        class="w-full px-3.5 py-2.5 bg-white border border-stone-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-stone-900 transition"
                        required
                    >
                </div>
            </div>

            <!-- Active Checkbox -->
            <div class="pt-1">
                <label class="flex items-center gap-2.5 cursor-pointer select-none">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        checked 
                        class="w-4 h-4 rounded border-stone-300 text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-700">Akun Aktif (Dapat Login ke Sistem)</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-stone-200/80 flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.users.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-semibold text-stone-600 hover:bg-stone-50 transition"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-sm transition flex items-center gap-2"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
