@extends('layouts.admin')

@section('title', 'Manajemen Bahan Baku — BrewPOS')
@section('page_title', 'Manajemen Bahan Baku')

@section('content')
<div class="space-y-5">
    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-lg text-xs font-semibold flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Total Bahan Baku</span>
                <i data-lucide="boxes" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-white tabular-nums">
                    {{ $rawMaterials->total() }}
                </div>
                <p class="text-[11px] text-stone-400 mt-0.5">Item terdaftar di gudang</p>
            </div>
        </div>

        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Hampir Habis</span>
                <i data-lucide="alert-circle" class="w-4 h-4 {{ ($warningCount ?? 0) > 0 ? 'text-amber-400' : 'text-stone-400' }}"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono tabular-nums {{ ($warningCount ?? 0) > 0 ? 'text-amber-400' : 'text-stone-200' }}">
                    {{ $warningCount ?? 0 }}
                </div>
                <p class="text-[11px] text-stone-400 mt-0.5">Mendekati batas minimum</p>
            </div>
        </div>

        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Stok Kritis</span>
                <i data-lucide="alert-triangle" class="w-4 h-4 {{ ($criticalCount ?? 0) > 0 ? 'text-rose-400' : 'text-stone-400' }}"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono tabular-nums {{ ($criticalCount ?? 0) > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ $criticalCount ?? 0 }}
                </div>
                <p class="text-[11px] text-stone-400 mt-0.5">Harus segera di-restock</p>
            </div>
        </div>

        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Estimasi Nilai Stok</span>
                <i data-lucide="wallet" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-emerald-400 tabular-nums">
                    Rp {{ number_format($totalInventoryValue ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[11px] text-stone-400 mt-0.5">Valuasi berbasis HPP unit</p>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="surface-card p-3.5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form method="GET" class="flex flex-1 flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full sm:w-auto">
                <input 
                    name="search" 
                    value="{{ request('search') }}"
                    class="form-input pl-8" 
                    placeholder="Cari nama bahan baku..." 
                    type="text"
                />
                <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-stone-500">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                </div>
            </div>

            <div class="sm:w-48">
                @php
                    $catOpts = collect($categories)->mapWithKeys(fn($c) => [$c => $c])->toArray();
                @endphp
                <x-dropdown-select 
                    name="category"
                    :options="$catOpts"
                    :selected="request('category')"
                    placeholder="Semua Kategori"
                />
            </div>

            <button type="submit" class="btn btn-sm btn-primary w-full sm:w-auto shrink-0">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('admin.raw-materials.index') }}" class="btn btn-sm btn-outline shrink-0" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm btn-secondary">
                <i data-lucide="history" class="w-3.5 h-3.5 text-stone-400"></i>
                <span>Log Mutasi</span>
            </a>
            <a href="{{ route('admin.restocks.create') }}" class="btn btn-sm btn-secondary">
                <i data-lucide="arrow-down-to-dot" class="w-3.5 h-3.5 text-amber-500"></i>
                <span>Catat Restock</span>
            </a>
            <a href="{{ route('admin.raw-materials.create') }}" class="btn btn-sm btn-primary">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Bahan</span>
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-200">
                <thead class="bg-[#141312] border-b border-[#2A2724] text-stone-400 font-semibold text-[11px]">
                    <tr>
                        <th class="px-4 py-3 min-w-[170px]">Nama Bahan</th>
                        <th class="px-4 py-3 whitespace-nowrap">Kategori</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Satuan</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Stok Saat Ini</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Min. Stok</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Harga / Unit</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap min-w-[120px]">Est. Nilai</th>
                        <th class="px-4 py-3 text-center whitespace-nowrap">Status</th>
                        <th class="px-4 py-3 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#22201D]">
                    @forelse($rawMaterials as $material)
                        @php
                            $status = $material->getStockStatus();
                            $statusBadgeClass = match($status) {
                                'Kritis' => 'badge-danger',
                                'Hampir Habis' => 'badge-warning',
                                default => 'badge-ready'
                            };
                            $stockColor = match($status) {
                                'Kritis' => 'text-rose-400 font-bold',
                                'Hampir Habis' => 'text-amber-400 font-semibold',
                                default => 'text-white'
                            };
                        @endphp
                        <tr class="hover:bg-[#1C1A18] transition-colors">
                            <td class="px-4 py-3 font-semibold text-white whitespace-nowrap">
                                {{ $material->name }}
                            </td>
                            <td class="px-4 py-3 text-stone-400 whitespace-nowrap">{{ $material->category ?? '-' }}</td>
                            <td class="px-4 py-3 text-center font-mono text-stone-400 whitespace-nowrap">{{ $material->unit }}</td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums whitespace-nowrap {{ $stockColor }}">
                                {{ number_format($material->stock_qty, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums text-stone-400 whitespace-nowrap">
                                {{ number_format($material->min_stock_qty, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums text-stone-200 whitespace-nowrap">
                                Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono tabular-nums font-semibold text-stone-200 whitespace-nowrap">
                                Rp {{ number_format($material->stock_qty * $material->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <span class="badge {{ $statusBadgeClass }}">
                                    @if($status === 'Kritis')
                                        <i data-lucide="alert-triangle" class="w-3 h-3 text-rose-400"></i>
                                    @elseif($status === 'Hampir Habis')
                                        <i data-lucide="alert-circle" class="w-3 h-3 text-amber-400"></i>
                                    @else
                                        <i data-lucide="check" class="w-3 h-3 text-emerald-400"></i>
                                    @endif
                                    <span>{{ $status }}</span>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openAdjustModal({{ $material->id }}, '{{ addslashes($material->name) }}', {{ $material->stock_qty }}, '{{ $material->unit }}')"
                                        class="btn btn-sm btn-secondary"
                                        title="Koreksi Stok Fisik"
                                    >
                                        <i data-lucide="sliders" class="w-3.5 h-3.5 text-stone-400"></i>
                                        <span>Koreksi</span>
                                    </button>

                                    <a 
                                        href="{{ route('admin.raw-materials.edit', $material) }}" 
                                        class="btn btn-sm btn-ghost p-1.5" 
                                        title="Edit Bahan Baku"
                                    >
                                        <i data-lucide="pencil" class="w-3.5 h-3.5 text-stone-400"></i>
                                    </a>

                                    <form action="{{ route('admin.raw-materials.destroy', $material) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus bahan baku {{ $material->name }}?')"
                                            class="btn btn-sm btn-ghost p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-950/30" 
                                            title="Hapus Bahan Baku"
                                        >
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-stone-400 space-y-2">
                                <i data-lucide="inbox" class="w-8 h-8 text-stone-600 mx-auto"></i>
                                <p class="font-semibold text-white">Belum ada data bahan baku.</p>
                                <p class="text-xs text-stone-500">Tambahkan bahan baku baru untuk memulai pencatatan inventori.</p>
                                <a href="{{ route('admin.raw-materials.create') }}" class="btn btn-sm btn-primary inline-flex mt-2">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <span>Tambah Bahan Pertama</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rawMaterials->hasPages())
            <div class="px-4 py-3 border-t border-[#2A2724] flex items-center justify-between text-xs text-stone-400">
                <span>
                    Menampilkan {{ $rawMaterials->firstItem() ?? 0 }}-{{ $rawMaterials->lastItem() ?? 0 }} dari {{ $rawMaterials->total() }} bahan baku
                </span>
                <div>
                    {{ $rawMaterials->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Koreksi Stok (Stock Adjustment) -->
<div id="adjustModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="surface-card max-w-md w-full overflow-hidden shadow-2xl">
        <div class="px-5 py-3.5 bg-[#141210] border-b border-[#2A2724] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-amber-500"></i>
                <div>
                    <h3 class="text-sm font-bold text-white">Koreksi Stok Bahan Baku</h3>
                    <p id="modalMaterialName" class="text-xs text-stone-400"></p>
                </div>
            </div>
            <button type="button" onclick="closeAdjustModal()" class="text-stone-400 hover:text-white p-1 rounded-md transition-colors">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="adjustForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            
            <div class="p-3 surface-card-inset text-xs border border-amber-500/20 text-stone-300">
                <p>Stok sistem saat ini: <strong id="modalCurrentStock" class="font-mono text-amber-400 font-bold"></strong></p>
                <p class="text-[11px] text-stone-400 mt-0.5">Masukkan jumlah stok fisik hasil audit stock opname terkini.</p>
            </div>

            <div>
                <label for="adjusted_qty" class="form-label">
                    Stok Fisik Baru <span id="modalUnitLabel" class="text-stone-400 font-normal"></span> <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="number" 
                    step="0.01" 
                    min="0" 
                    name="adjusted_qty" 
                    id="adjusted_qty" 
                    required 
                    placeholder="0" 
                    class="form-input font-mono font-bold"
                >
            </div>

            <div>
                <label for="adjust_reason" class="form-label">
                    Alasan Penyesuaian <span class="text-rose-400">*</span>
                </label>
                <textarea 
                    name="reason" 
                    id="adjust_reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Selisih stock opname mingguan / bahan tumpah / expired" 
                    class="form-input"
                ></textarea>
            </div>

            <div class="flex items-center gap-2.5 pt-1">
                <button 
                    type="button" 
                    onclick="closeAdjustModal()" 
                    class="btn btn-md btn-secondary flex-1"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="btn btn-md btn-primary flex-1"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Simpan Koreksi</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAdjustModal(id, name, currentStock, unit) {
        const modal = document.getElementById('adjustModal');
        const form = document.getElementById('adjustForm');
        form.action = `/admin/raw-materials/${id}/adjust`;
        document.getElementById('modalMaterialName').textContent = name;
        document.getElementById('modalCurrentStock').textContent = `${currentStock} ${unit}`;
        document.getElementById('modalUnitLabel').textContent = `(${unit})`;
        document.getElementById('adjusted_qty').value = currentStock;
        document.getElementById('adjust_reason').value = '';
        modal.classList.remove('hidden');
        if (window.lucide) lucide.createIcons();
    }

    function closeAdjustModal() {
        document.getElementById('adjustModal').classList.add('hidden');
    }
</script>
@endsection
