@extends('layouts.admin')

@section('title', 'Laporan & Analitik — BrewPOS')
@section('page_title', 'Laporan & Analitik')

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="surface-card p-4 sm:p-5">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="form-label mb-1.5">Periode Laporan</label>
                    <x-dropdown-select 
                        name="period"
                        :options="[
                            'today' => 'Hari Ini',
                            'this_week' => 'Minggu Ini',
                            'this_month' => 'Bulan Ini',
                            'last_30_days' => '30 Hari Terakhir',
                            'custom' => 'Kustom',
                        ]"
                        :selected="$filters['period'] ?? 'this_month'"
                    />
                </div>

                <div>
                    <label class="form-label mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start" value="{{ optional($filters['start'])->format('Y-m-d') }}" class="form-input" />
                </div>

                <div>
                    <label class="form-label mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end" value="{{ optional($filters['end'])->format('Y-m-d') }}" class="form-input" />
                </div>

                <div>
                    <button type="submit" class="btn btn-primary w-full text-xs flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Main Report Card -->
        <div class="surface-card overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="border-b border-stone-800 p-3 sm:p-4 bg-stone-900/50">
                <nav class="flex flex-wrap gap-2">
                    @php
                        $tabs = [
                            'sales' => 'Penjualan',
                            'raw_material' => 'Bahan Baku',
                            'hpp_margin' => 'HPP & Margin',
                            'cashier_session' => 'Rekap Sesi',
                        ];
                    @endphp
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('admin.reports.sales', array_merge(request()->only(['period', 'start', 'end']), ['tab' => $key])) }}" class="px-3.5 py-2 text-xs rounded-lg transition flex items-center gap-2 {{ $activeTab === $key ? 'bg-amber-600 text-stone-950 font-bold' : 'bg-stone-900/40 text-stone-400 hover:text-stone-200 border border-stone-800' }}">
                            @if($key === 'sales')
                                <i data-lucide="dollar-sign" class="w-3.5 h-3.5"></i>
                            @elseif($key === 'raw_material')
                                <i data-lucide="boxes" class="w-3.5 h-3.5"></i>
                            @elseif($key === 'hpp_margin')
                                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                            @else
                                <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                            @endif
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="p-5 sm:p-6">
                <!-- Tab Header & Export Actions -->
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6 pb-4 border-b border-stone-800">
                    <div>
                        <h3 class="text-sm font-semibold text-stone-100 uppercase tracking-wider">{{ $tabs[$activeTab] ?? 'Penjualan' }}</h3>
                        <p class="text-xs text-stone-400 mt-0.5">Rentang Periode: <span class="font-mono text-stone-300">{{ $filters['start']->format('d M Y') }} – {{ $filters['end']->format('d M Y') }}</span></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if(in_array($activeTab, ['sales']))
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel'])) }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Export Excel</span>
                            </a>
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf'])) }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-rose-400"></i>
                                <span>Export PDF</span>
                            </a>
                        @else
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel', 'section' => $activeTab])) }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-emerald-400"></i>
                                <span>Export Excel</span>
                            </a>
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf', 'section' => $activeTab])) }}" class="btn btn-secondary text-xs inline-flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-rose-400"></i>
                                <span>Export PDF</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if($activeTab === 'sales')
                    <!-- KPI Summary Cards -->
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                        <div class="stat-tile">
                            <div class="stat-tile-label">Total Pendapatan</div>
                            <div class="stat-tile-value">Rp {{ number_format($salesSummary['total_revenue'], 0, ',', '.') }}</div>
                            <div class="stat-tile-meta">Dari transaksi terbayar</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-tile-label">Jumlah Transaksi</div>
                            <div class="stat-tile-value"><p class="mt-4 text-3xl font-semibold text-slate-900">{{ $salesSummary['transaction_count'] }}</p></div>
                            <div class="stat-tile-meta">Total order selesai</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-tile-label">Rata-rata Order (AOV)</div>
                            <div class="stat-tile-value">Rp {{ number_format($salesSummary['average_order_value'], 0, ',', '.') }}</div>
                            <div class="stat-tile-meta">Nilai per transaksi</div>
                        </div>
                        <div class="stat-tile">
                            <div class="stat-tile-label">Bahan Kritis</div>
                            <div class="stat-tile-value {{ $salesSummary['critical_stock_count'] > 0 ? 'text-rose-400' : '' }}">{{ $salesSummary['critical_stock_count'] }}</div>
                            <div class="stat-tile-meta">Stok di bawah minimum</div>
                        </div>
                    </div>

                    <!-- Top Products Table -->
                    <div class="rounded-xl border border-stone-800 overflow-hidden">
                        <div class="px-5 py-3 border-b border-stone-800 bg-stone-900/60 flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-stone-300 uppercase tracking-wider">Produk Menu Terlaris</h4>
                            <span class="text-[11px] text-stone-500 font-mono">Berdasarkan kuantitas terjual</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-stone-800 bg-stone-900/40 text-stone-400 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3">Produk</th>
                                        <th class="px-5 py-3 text-right">Terjual</th>
                                        <th class="px-5 py-3 text-right">Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-800">
                                    @forelse($topProducts as $item)
                                        <tr class="hover:bg-stone-800/40 transition">
                                            <td class="px-5 py-3.5 font-medium text-stone-200">{{ $item->product->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">{{ $item->quantity_sold }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">Rp {{ number_format($item->revenue, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-5 py-8 text-center text-stone-500">Belum ada data penjualan pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'raw_material')
                    <div class="rounded-xl border border-stone-800 overflow-hidden">
                        <div class="px-5 py-3 border-b border-stone-800 bg-stone-900/60 flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-stone-300 uppercase tracking-wider">Pemakaian Bahan Baku</h4>
                            <span class="text-[11px] text-stone-500 font-mono">Deduksi dari resep transaksi</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-stone-800 bg-stone-900/40 text-stone-400 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3">Bahan Baku</th>
                                        <th class="px-5 py-3 text-right">Qty Digunakan</th>
                                        <th class="px-5 py-3 text-right">Total Biaya</th>
                                        <th class="px-5 py-3 text-right">Biaya / Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-800">
                                    @forelse($rawMaterialUsage as $item)
                                        <tr class="hover:bg-stone-800/40 transition">
                                            <td class="px-5 py-3.5 font-medium text-stone-200">{{ $item->raw_material->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">{{ number_format($item->qty_used, 2, ',', '.') }} <span class="text-stone-400 font-sans text-[11px]">{{ $item->raw_material->unit ?? '' }}</span></td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono text-stone-400">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-8 text-center text-stone-500">Tidak ada pemakaian bahan selama periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'hpp_margin')
                    <div class="rounded-xl border border-stone-800 overflow-hidden">
                        <div class="px-5 py-3 border-b border-stone-800 bg-stone-900/60 flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-stone-300 uppercase tracking-wider">HPP & Margin per Menu</h4>
                            <span class="text-[11px] text-stone-500 font-mono">Berdasarkan komposisi resep aktif</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-stone-800 bg-stone-900/40 text-stone-400 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3">Produk</th>
                                        <th class="px-5 py-3 text-center">Ukuran</th>
                                        <th class="px-5 py-3 text-right">Harga Jual</th>
                                        <th class="px-5 py-3 text-right">HPP</th>
                                        <th class="px-5 py-3 text-right">Margin Rp</th>
                                        <th class="px-5 py-3 text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-800">
                                    @forelse($hppMargin as $item)
                                        <tr class="hover:bg-stone-800/40 transition">
                                            <td class="px-5 py-3.5 font-medium text-stone-200">{{ $item->product_name }}</td>
                                            <td class="px-5 py-3.5 text-center">
                                                <span class="badge badge-stone">
                                                    {{ strtoupper($item->size_variant) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono text-stone-300">Rp {{ number_format($item->hpp, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-emerald-400">Rp {{ number_format($item->margin_rp, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-emerald-400">{{ number_format($item->margin_pct, 2, ',', '.') }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-8 text-center text-stone-500">Tidak ada produk dengan resep terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-stone-800 overflow-hidden">
                        <div class="px-5 py-3 border-b border-stone-800 bg-stone-900/60 flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-stone-300 uppercase tracking-wider">Rekap Sesi Kasir</h4>
                            <span class="text-[11px] text-stone-500 font-mono">Shift & rekonsiliasi kas</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-stone-800 bg-stone-900/40 text-stone-400 uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3">Sesi</th>
                                        <th class="px-5 py-3">Kasir</th>
                                        <th class="px-5 py-3">Dibuka</th>
                                        <th class="px-5 py-3">Ditutup</th>
                                        <th class="px-5 py-3 text-right">Total Penjualan</th>
                                        <th class="px-5 py-3 text-center">Jumlah Transaksi</th>
                                        <th class="px-5 py-3 text-right">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-800">
                                    @forelse($cashierSessions as $session)
                                        <tr class="hover:bg-stone-800/40 transition">
                                            <td class="px-5 py-3.5 font-mono text-stone-300 font-medium">#{{ $session->id }}</td>
                                            <td class="px-5 py-3.5 font-medium text-stone-200">{{ $session->user_name }}</td>
                                            <td class="px-5 py-3.5 font-mono text-stone-400 text-[11px]">{{ optional($session->opened_at)->format('d M Y H:i') }}</td>
                                            <td class="px-5 py-3.5 font-mono text-stone-400 text-[11px]">{{ optional($session->closed_at)->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium text-stone-200">Rp {{ number_format($session->total_sales, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-center font-mono font-medium text-stone-300">{{ $session->orders_count }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-medium {{ $session->difference < 0 ? 'text-rose-400' : ($session->difference > 0 ? 'text-emerald-400' : 'text-stone-300') }}">
                                                Rp {{ number_format($session->difference, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-5 py-8 text-center text-stone-500">Tidak ada sesi kasir pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
