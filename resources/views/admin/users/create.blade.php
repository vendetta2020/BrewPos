@extends('layouts.admin')

@section('title', 'Tambah Pengguna — BrewPOS')
@section('page_title', 'Tambah Pengguna Baru')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg p-6 space-y-6">
        <div class="border-b border-[#272320] pb-4">
            <h2 class="text-base font-bold text-white tracking-tight">Informasi Akun Karyawan</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Buat kredensial login untuk administrator, kasir (POS), atau barista (KDS).</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Nama Lengkap <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="text" 
                    name="name" 
                    id="name" 
                    value="{{ old('name') }}" 
                    placeholder="Contoh: Budi Santoso"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white placeholder-stone-600 transition"
                    required
                >
                @error('name')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Email -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Email Akun <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="email" 
                    name="email" 
                    id="email" 
                    value="{{ old('email') }}" 
                    placeholder="nama@brewpos.test"
                    class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-mono font-medium text-white placeholder-stone-600 transition"
                    required
                >
                @error('email')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Role -->
            <div class="space-y-1.5">
                <label for="role" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                    Peran / Hak Akses (Role) <span class="text-rose-400">*</span>
                </label>
                <x-dropdown-select 
                    name="role" 
                    id="role" 
                    :options="[
                        'admin' => 'Admin (Akses Penuh Manajemen)',
                        'cashier' => 'Kasir (Layar Transaksi POS)',
                        'barista' => 'Barista (Layar Dapur KDS)',
                    ]"
                    placeholder="-- Pilih Peran --"
                    required
                />
                @error('role')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <!-- Password -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Kata Sandi <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password" 
                        placeholder="Minimal 6 karakter"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition placeholder-stone-600"
                        required
                    >
                    @error('password')<p class="text-xs text-rose-400 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirmation" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider">
                        Konfirmasi Kata Sandi <span class="text-rose-400">*</span>
                    </label>
                    <input 
                        type="password" 
                        name="password_confirmation" 
                        id="password_confirmation" 
                        placeholder="Ulangi kata sandi"
                        class="w-full px-3.5 py-2.5 bg-[#141312] border border-[#2D2A26] focus:border-amber-500 focus:ring-1 focus:ring-amber-500 rounded-xl text-xs font-medium text-white transition placeholder-stone-600"
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
                        class="w-4 h-4 rounded border-[#2D2A26] bg-[#141312] text-amber-600 focus:ring-amber-500"
                    >
                    <span class="text-xs font-medium text-stone-300">Akun Aktif (Dapat Login ke Sistem)</span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-[#272320] flex items-center justify-end gap-3">
                <a 
                    href="{{ route('admin.users.index') }}" 
                    class="px-4 py-2.5 rounded-xl border border-[#2D2A26] text-xs font-semibold text-stone-300 hover:bg-[#24211E] transition"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white font-bold text-xs uppercase tracking-wider shadow-md shadow-amber-600/20 transition flex items-center gap-2"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Pengguna</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
