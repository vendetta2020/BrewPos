@extends('layouts.admin')

@section('title', 'Kategori Menu — BrewPOS')
@section('page_title', 'Kategori Produk')

@section('content')
<div class="space-y-5">
    <!-- Header Action Bar -->
    <div class="surface-card p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h2 class="text-sm font-bold text-white">Daftar Kategori Menu</h2>
            <p class="text-xs text-stone-400 mt-0.5">Pengelompokan menu untuk mempermudah navigasi kasir di layar POS.</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary shrink-0">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Kategori</span>
        </a>
    </div>

    <!-- Table Card -->
    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            @if($categories->count())
                <table class="w-full text-xs text-left text-stone-200">
                    <thead class="bg-[#141312] border-b border-[#2A2724] font-semibold text-stone-400">
                        <tr>
                            <th class="px-4 py-3">Nama Kategori</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#22201D]">
                        @foreach($categories as $cat)
                            <tr class="hover:bg-[#1C1A18] transition-colors">
                                <td class="px-4 py-3 font-semibold text-white">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="tag" class="w-3.5 h-3.5 text-stone-500"></i>
                                        <span>{{ $cat->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($cat->is_active)
                                        <span class="badge badge-ready">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="badge badge-neutral">
                                            <span class="w-1.5 h-1.5 rounded-full bg-stone-500"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        <a 
                                            href="{{ route('admin.categories.edit', $cat) }}" 
                                            class="btn btn-sm btn-secondary"
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
                                                class="btn btn-sm btn-destructive"
                                                title="Hapus Kategori"
                                            >
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="text-center py-12 px-4 space-y-2">
                    <i data-lucide="tags" class="w-8 h-8 text-stone-600 mx-auto"></i>
                    <h3 class="text-sm font-bold text-white">Belum Ada Kategori</h3>
                    <p class="text-xs text-stone-400 max-w-sm mx-auto">Tambahkan kategori produk seperti Coffee, Tea, Non-Coffee, atau Pastry.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
