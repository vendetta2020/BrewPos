@extends('layouts.admin')

@section('title', 'Manajemen Bahan Baku — BrewPOS')
@section('page_title', 'Manajemen Bahan Baku')

@section('content')
<div class="space-y-6">
    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="p-3.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-xl text-xs font-semibold flex items-center gap-2.5">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 bg-rose-500/10 border border-rose-500/20 text-rose-400 rounded-xl text-xs font-semibold flex items-center gap-2.5">
            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Summary Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-[#181716] rounded-2xl p-5 border border-[#2D2A26] shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#A8A29E] uppercase tracking-wider">Total Bahan Baku</p>
                    <p class="text-2xl font-bold font-mono text-white mt-1">{{ $rawMaterials->total() }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400 flex items-center justify-center">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-[#181716] rounded-2xl p-5 border border-[#2D2A26] shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#A8A29E] uppercase tracking-wider">Hampir Habis</p>
                    <p class="text-2xl font-bold font-mono text-amber-400 mt-1">{{ $warningCount ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-[#181716] rounded-2xl p-5 border border-[#2D2A26] shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#A8A29E] uppercase tracking-wider">Stok Kritis</p>
                    <p class="text-2xl font-bold font-mono text-rose-400 mt-1">{{ $criticalCount ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
            </div>
        </div>

        <div class="bg-[#181716] rounded-2xl p-5 border border-[#2D2A26] shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-[#A8A29E] uppercase tracking-wider">Estimasi Nilai Stok</p>
                    <p class="text-xl font-bold font-mono text-emerald-400 mt-1">Rp {{ number_format($totalInventoryValue ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="bg-[#181716] rounded-2xl p-4 sm:p-5 border border-[#2D2A26] shadow-lg flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <form method="GET" class="flex flex-1 flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input 
                    name="search" 
                    value="{{ request('search') }}"
                    class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white placeholder-stone-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500" 
                    placeholder="Cari nama bahan baku..." 
                    type="text"
                />
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-500">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
            </div>

            <select name="category" class="px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                <option value="" class="bg-[#181716] text-stone-400">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @if(request('category') === $category) selected @endif class="bg-[#181716] text-white">{{ $category }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('admin.raw-materials.index') }}" class="px-3 py-2.5 bg-[#24211E] hover:bg-[#2D2A26] text-stone-300 border border-[#2D2A26] text-xs font-semibold rounded-xl transition flex items-center justify-center">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </form>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.inventory.movements') }}" class="px-3.5 py-2.5 border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="history" class="w-4 h-4 text-stone-400"></i>
                <span>Log Mutasi</span>
            </a>
            <a href="{{ route('admin.restocks.create') }}" class="px-3.5 py-2.5 border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] text-stone-200 text-xs font-semibold rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="arrow-down-to-dot" class="w-4 h-4 text-amber-500"></i>
                <span>Catat Restock</span>
            </a>
            <a href="{{ route('admin.raw-materials.create') }}" class="px-4 py-2.5 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-md shadow-amber-600/20 transition flex items-center gap-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Tambah Bahan</span>
            </a>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-stone-200">
                <thead class="bg-[#141210] border-b border-[#2D2A26] text-[#A8A29E] font-semibold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-5 py-3.5">Nama Bahan</th>
                        <th class="px-5 py-3.5">Kategori</th>
                        <th class="px-5 py-3.5 text-center">Satuan</th>
                        <th class="px-5 py-3.5 text-right">Stok Saat Ini</th>
                        <th class="px-5 py-3.5 text-right">Min. Stok</th>
                        <th class="px-5 py-3.5 text-right">Harga / Unit</th>
                        <th class="px-5 py-3.5 text-right">Est. Nilai</th>
                        <th class="px-5 py-3.5 text-center">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#272320]">
                    @forelse($rawMaterials as $material)
                        @php
                            $status = $material->getStockStatus();
                            $statusBadge = match($status) {
                                'Kritis' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                'Hampir Habis' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                default => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                            };
                            $stockColor = match($status) {
                                'Kritis' => 'text-rose-400 font-extrabold',
                                'Hampir Habis' => 'text-amber-400 font-bold',
                                default => 'text-white font-bold'
                            };
                        @endphp
                        <tr class="hover:bg-[#1E1C1A] transition-colors">
                            <td class="px-5 py-3.5 font-bold text-white">
                                {{ $material->name }}
                            </td>
                            <td class="px-5 py-3.5 text-[#A8A29E]">{{ $material->category ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-center font-mono text-[#A8A29E]">{{ $material->unit }}</td>
                            <td class="px-5 py-3.5 text-right font-mono {{ $stockColor }}">
                                {{ number_format($material->stock_qty, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-[#A8A29E]">
                                {{ number_format($material->min_stock_qty, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono text-white">
                                Rp {{ number_format($material->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-mono font-semibold text-white">
                                Rp {{ number_format($material->stock_qty * $material->cost_per_unit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $statusBadge }}">
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
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Tombol Koreksi Stok (Adjust) -->
                                    <button 
                                        type="button" 
                                        onclick="openAdjustModal({{ $material->id }}, '{{ addslashes($material->name) }}', {{ $material->stock_qty }}, '{{ $material->unit }}')"
                                        class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 transition flex items-center gap-1"
                                        title="Koreksi Stok Fisik"
                                    >
                                        <i data-lucide="sliders" class="w-3 h-3 text-amber-400"></i>
                                        <span>Koreksi</span>
                                    </button>

                                    <!-- Tombol Edit -->
                                    <a 
                                        href="{{ route('admin.raw-materials.edit', $material) }}" 
                                        class="p-1.5 text-stone-400 hover:text-white hover:bg-[#2D2A26] rounded-lg transition" 
                                        title="Edit Bahan Baku"
                                    >
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('admin.raw-materials.destroy', $material) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus bahan baku {{ $material->name }}?')"
                                            class="p-1.5 text-stone-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition" 
                                            title="Hapus Bahan Baku"
                                        >
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-[#A8A29E]">
                                <div class="w-12 h-12 bg-[#141210] border border-[#2D2A26] rounded-2xl flex items-center justify-center mx-auto mb-2 text-amber-500">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <p class="font-semibold text-white">Belum ada data bahan baku.</p>
                                <p class="text-xs text-[#A8A29E] mt-1">Tambahkan bahan baku baru untuk memulai pencatatan inventori.</p>
                                <a href="{{ route('admin.raw-materials.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 mt-3 bg-amber-600 hover:bg-amber-500 text-white rounded-xl text-xs font-bold transition">
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
            <div class="px-6 py-4 border-t border-[#272320] flex items-center justify-between bg-[#141210]">
                <span class="text-xs text-[#A8A29E]">
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
<div id="adjustModal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-[#181716] rounded-2xl shadow-2xl max-w-md w-full overflow-hidden border border-[#2D2A26]">
        <div class="px-6 py-4 bg-[#141210] border-b border-[#272320] flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center font-bold">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Koreksi Stok Bahan Baku</h3>
                    <p id="modalMaterialName" class="text-xs text-[#A8A29E] font-medium"></p>
                </div>
            </div>
            <button type="button" onclick="closeAdjustModal()" class="text-stone-400 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="adjustForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            
            <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-xs text-amber-300">
                <p>Stok sistem saat ini: <strong id="modalCurrentStock" class="font-mono text-amber-400 font-bold"></strong></p>
                <p class="text-[11px] text-[#A8A29E] mt-0.5">Masukkan jumlah stok fisik sebenarnya hasil stock opname.</p>
            </div>

            <div>
                <label for="adjusted_qty" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">
                    Stok Fisik Baru <span id="modalUnitLabel" class="text-stone-400 font-normal"></span> <span class="text-rose-400">*</span>
                </label>
                <input 
                    type="number" 
                    step="0.01" 
                    min="0" 
                    name="adjusted_qty" 
                    id="adjusted_qty" 
                    required 
                    placeholder="0.00" 
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-mono font-bold"
                >
            </div>

            <div>
                <label for="adjust_reason" class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">
                    Alasan Penyesuaian <span class="text-rose-400">*</span>
                </label>
                <textarea 
                    name="reason" 
                    id="adjust_reason" 
                    rows="2" 
                    required 
                    placeholder="Contoh: Selisih stock opname mingguan / bahan tumpah / expired" 
                    class="w-full px-3.5 py-2.5 text-xs rounded-xl bg-[#141312] border border-[#2D2A26] text-white placeholder-stone-600 focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                ></textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button 
                    type="button" 
                    onclick="closeAdjustModal()" 
                    class="flex-1 py-2.5 border border-[#2D2A26] text-stone-300 hover:bg-[#24211E] font-semibold text-xs rounded-xl transition"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="flex-1 py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-amber-600/20 transition flex items-center justify-center gap-1.5"
                >
                    <i data-lucide="check" class="w-4 h-4"></i>
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
