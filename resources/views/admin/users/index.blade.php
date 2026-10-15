@extends('layouts.admin')

@section('title', 'Manajemen Pengguna — BrewPOS')
@section('page_title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Daftar Akun Pengguna & Karyawan</h2>
            <p class="text-xs text-stone-500 mt-0.5">Kelola akses role hak istimewa (Admin, Kasir, Barista) dan status keaktifan akun.</p>
        </div>
        <a 
            href="{{ route('admin.users.create') }}" 
            class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm flex items-center gap-2 shrink-0"
        >
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    <!-- Users Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            @if($users->count())
                <table class="w-full text-xs text-left text-stone-700">
                    <thead class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama & Profil</th>
                            <th class="px-5 py-3.5">Email Akun</th>
                            <th class="px-5 py-3.5 text-center">Peran (Role)</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($users as $user)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-stone-900">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-stone-900 text-amber-400 font-bold flex items-center justify-center text-xs shrink-0 border border-stone-700">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-stone-900">{{ $user->name }}</div>
                                            <div class="text-[10px] text-stone-400 font-mono">ID: #{{ $user->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-stone-600 text-xs">
                                    {{ $user->email }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            <i data-lucide="shield-alert" class="w-3 h-3 text-rose-500"></i>
                                            Admin
                                        </span>
                                    @elseif($user->role === 'cashier')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <i data-lucide="monitor" class="w-3 h-3 text-emerald-500"></i>
                                            Kasir (POS)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i data-lucide="coffee" class="w-3 h-3 text-purple-500"></i>
                                            Barista (KDS)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.users.edit', $user) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-lg text-xs transition"
                                        title="Edit Pengguna"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-500"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini?')" 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition"
                                            title="Hapus Pengguna"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <!-- Clean Empty State -->
                <div class="text-center py-12 px-4">
                    <div class="w-14 h-14 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-600">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-stone-900">Belum Ada Pengguna</h3>
                    <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Tambahkan akun kasir atau barista untuk mengoperasikan sistem.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
