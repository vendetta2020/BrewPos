@extends('layouts.admin')

@section('title', 'Kategori Menu — BrewPOS')
@section('page_title', 'Kategori Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-white rounded-2xl border border-stone-200/80 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-stone-900 tracking-tight">Daftar Kategori Menu</h2>
            <p class="text-xs text-stone-500 mt-0.5">Pengelompokan menu untuk mempermudah navigasi kasir di layar POS.</p>
        </div>
        <a 
            href="{{ route('admin.categories.create') }}" 
            class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-sm flex items-center gap-2 shrink-0"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Kategori</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            @if($categories->count())
                <table class="w-full text-xs text-left text-stone-700">
                    <thead class="bg-stone-50 border-b border-stone-200 font-semibold text-stone-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Kategori</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-stone-50/70 transition-colors">
                                <td class="px-5 py-3.5 font-bold text-stone-900">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-600">
                                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span>{{ $cat->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-stone-100 text-stone-600 border border-stone-200' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cat->is_active ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.categories.edit', $cat) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-lg text-xs transition"
                                        title="Edit Kategori"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-500"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-lg text-xs transition"
                                            title="Hapus Kategori"
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
                        <i data-lucide="tags" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-stone-900">Belum Ada Kategori</h3>
                    <p class="text-xs text-stone-500 mt-1 max-w-sm mx-auto">Tambahkan kategori produk seperti Coffee, Tea, Non-Coffee, atau Pastry.</p>
                    <div class="mt-4">
                        <a 
                            href="{{ route('admin.categories.create') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow-xs"
                        >
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Tambah Kategori Sekarang</span>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
