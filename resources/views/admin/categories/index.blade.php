@extends('layouts.admin')

@section('title', 'Kategori Menu — BrewPOS')
@section('page_title', 'Kategori Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Summary -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h2 class="text-base font-bold text-white tracking-tight">Daftar Kategori Menu</h2>
            <p class="text-xs text-[#A8A29E] mt-0.5">Pengelompokan menu untuk mempermudah navigasi kasir di layar POS.</p>
        </div>
        <a 
            href="{{ route('admin.categories.create') }}" 
            class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center gap-2 shrink-0"
        >
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Kategori</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            @if($categories->count())
                <table class="w-full text-xs text-left text-stone-200">
                    <thead class="bg-[#141210] border-b border-[#2D2A26] font-semibold text-[#A8A29E] uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3.5">Nama Kategori</th>
                            <th class="px-5 py-3.5 text-center">Status</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#272320]">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-[#1E1C1A] transition-colors">
                                <td class="px-5 py-3.5 font-bold text-white">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                        </div>
                                        <span>{{ $cat->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $cat->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-[#141312] text-stone-400 border border-[#2D2A26]' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cat->is_active ? 'bg-emerald-500' : 'bg-stone-500' }}"></span>
                                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right space-x-1">
                                    <a 
                                        href="{{ route('admin.categories.edit', $cat) }}" 
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 border border-[#2D2A26] font-semibold rounded-lg text-xs transition"
                                        title="Edit Kategori"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-400"></i>
                                        <span>Edit</span>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" 
                                            type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 font-semibold rounded-lg text-xs transition"
                                            title="Hapus Kategori"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-400"></i>
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
                    <div class="w-14 h-14 bg-[#141210] border border-[#2D2A26] rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-500">
                        <i data-lucide="tags" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Belum Ada Kategori</h3>
                    <p class="text-xs text-[#A8A29E] mt-1 max-w-sm mx-auto">Tambahkan kategori produk seperti Coffee, Tea, Non-Coffee, atau Pastry.</p>
                    <div class="mt-4">
                        <a 
                            href="{{ route('admin.categories.create') }}" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold rounded-xl transition shadow-md shadow-amber-600/20"
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
