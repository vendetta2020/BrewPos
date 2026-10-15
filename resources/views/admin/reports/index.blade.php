@extends('layouts.admin')

@section('title', 'Laporan & Analitik — BrewPOS')
@section('page_title', 'Laporan & Analitik')

@section('content')
    <div class="space-y-6">
        <!-- Filter Bar -->
        <div class="bg-[#181716] rounded-2xl border border-[#2D2A26] p-4 sm:p-5 shadow-lg">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Periode Laporan</label>
                    <select name="period" class="w-full px-3.5 py-2.5 text-xs bg-[#141312] border border-[#2D2A26] text-white rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        @foreach(['today' => 'Hari Ini', 'this_week' => 'Minggu Ini', 'this_month' => 'Bulan Ini', 'last_30_days' => '30 Hari Terakhir', 'custom' => 'Kustom'] as $value => $label)
                            <option value="{{ $value }}" {{ $filters['period'] === $value ? 'selected' : '' }} class="bg-[#181716] text-white">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                    <input type="date" name="start" value="{{ optional($filters['start'])->format('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs bg-[#141312] border border-[#2D2A26] text-white rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500" />
                </div>

                <div>
                    <label class="block text-xs font-bold text-[#A8A29E] uppercase tracking-wider mb-1.5">Tanggal Selesai</label>
                    <input type="date" name="end" value="{{ optional($filters['end'])->format('Y-m-d') }}" class="w-full px-3.5 py-2.5 text-xs bg-[#141312] border border-[#2D2A26] text-white rounded-xl focus:border-amber-500 focus:ring-1 focus:ring-amber-500" />
                </div>

                <div>
                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-500 active:scale-[0.99] text-white text-xs font-bold uppercase tracking-wider rounded-xl transition shadow-md shadow-amber-600/20 flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan Filter</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Main Report Card -->
        <div class="bg-[#181716] shadow-lg rounded-2xl border border-[#2D2A26] overflow-hidden">
            <!-- Navigation Tabs -->
            <div class="border-b border-[#272320] p-4 bg-[#141210]">
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
                        <a href="{{ route('admin.reports.sales', array_merge(request()->only(['period', 'start', 'end']), ['tab' => $key])) }}" class="rounded-xl px-4 py-2 text-xs font-bold transition flex items-center gap-2 {{ $activeTab === $key ? 'bg-amber-600 text-white shadow-md shadow-amber-600/20' : 'bg-[#181716] text-[#A8A29E] hover:text-white border border-[#2D2A26]' }}">
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

            <div class="p-6">
                <!-- Tab Header & Export Actions -->
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6 pb-4 border-b border-[#272320]">
                    <div>
                        <h3 class="text-base font-bold text-white tracking-tight">{{ $tabs[$activeTab] ?? 'Penjualan' }}</h3>
                        <p class="text-xs text-[#A8A29E] mt-0.5">Rentang Periode: <span class="font-mono text-amber-400">{{ $filters['start']->format('d M Y') }} – {{ $filters['end']->format('d M Y') }}</span></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if(in_array($activeTab, ['sales']))
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel'])) }}" class="rounded-xl border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] px-3.5 py-2 text-xs font-semibold text-emerald-400 transition flex items-center gap-1.5">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                <span>Export Excel</span>
                            </a>
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf'])) }}" class="rounded-xl border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] px-3.5 py-2 text-xs font-semibold text-rose-400 transition flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                <span>Export PDF</span>
                            </a>
                        @else
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel', 'section' => $activeTab])) }}" class="rounded-xl border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] px-3.5 py-2 text-xs font-semibold text-emerald-400 transition flex items-center gap-1.5">
                                <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                                <span>Export Excel</span>
                            </a>
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf', 'section' => $activeTab])) }}" class="rounded-xl border border-[#2D2A26] bg-[#24211E] hover:bg-[#2D2A26] px-3.5 py-2 text-xs font-semibold text-rose-400 transition flex items-center gap-1.5">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                <span>Export PDF</span>
                            </a>
                        @endif
                    </div>
                </div>

                @if($activeTab === 'sales')
                    <!-- KPI Summary Cards -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                        <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] p-5 shadow-sm">
                            <div class="flex items-center justify-between text-[#A8A29E] text-xs font-semibold">
                                <span>Total Pendapatan</span>
                                <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                                    <i data-lucide="wallet" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <p class="mt-3 text-2xl font-bold font-mono text-white">Rp {{ number_format($salesSummary['total_revenue'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] p-5 shadow-sm">
                            <div class="flex items-center justify-between text-[#A8A29E] text-xs font-semibold">
                                <span>Jumlah Transaksi</span>
                                <div class="w-7 h-7 rounded-lg bg-sky-500/10 border border-sky-500/20 flex items-center justify-center text-sky-400">
                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $salesSummary['transaction_count'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] p-5 shadow-sm">
                            <div class="flex items-center justify-between text-[#A8A29E] text-xs font-semibold">
                                <span>Rata-rata Order (AOV)</span>
                                <div class="w-7 h-7 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400">
                                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <p class="mt-3 text-2xl font-bold font-mono text-white">Rp {{ number_format($salesSummary['average_order_value'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] p-5 shadow-sm">
                            <div class="flex items-center justify-between text-[#A8A29E] text-xs font-semibold">
                                <span>Bahan Kritis</span>
                                <div class="w-7 h-7 rounded-lg bg-rose-500/10 border border-rose-500/20 flex items-center justify-center text-rose-400">
                                    <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                                </div>
                            </div>
                            <p class="mt-3 text-2xl font-bold font-mono {{ $salesSummary['critical_stock_count'] > 0 ? 'text-rose-400' : 'text-white' }}">{{ $salesSummary['critical_stock_count'] }}</p>
                        </div>
                    </div>

                    <!-- Top Products Table -->
                    <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[#272320]">
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                                <span>Produk Menu Terlaris</span>
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-[#2D2A26] bg-[#181716] text-[#A8A29E] uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3.5">Produk</th>
                                        <th class="px-5 py-3.5 text-center">Terjual</th>
                                        <th class="px-5 py-3.5 text-right">Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#272320]">
                                    @forelse($topProducts as $item)
                                        <tr class="hover:bg-[#1E1C1A] transition">
                                            <td class="px-5 py-3.5 font-bold text-white">{{ $item->product->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-5 py-3.5 text-center font-mono font-bold text-amber-400">{{ $item->quantity_sold }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-white">Rp {{ number_format($item->revenue, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-5 py-6 text-center text-[#A8A29E]">Belum ada data penjualan pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'raw_material')
                    <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[#272320]">
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="boxes" class="w-4 h-4 text-amber-500"></i>
                                <span>Pemakaian Bahan Baku</span>
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-[#2D2A26] bg-[#181716] text-[#A8A29E] uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3.5">Bahan Baku</th>
                                        <th class="px-5 py-3.5 text-right">Qty Digunakan</th>
                                        <th class="px-5 py-3.5 text-right">Total Biaya</th>
                                        <th class="px-5 py-3.5 text-right">Biaya / Unit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#272320]">
                                    @forelse($rawMaterialUsage as $item)
                                        <tr class="hover:bg-[#1E1C1A] transition">
                                            <td class="px-5 py-3.5 font-bold text-white">{{ $item->raw_material->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono text-amber-400 font-bold">{{ number_format($item->qty_used, 2, ',', '.') }} {{ $item->raw_material->unit ?? '' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-white">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono text-[#A8A29E]">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-5 py-6 text-center text-[#A8A29E]">Tidak ada pemakaian bahan selama periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'hpp_margin')
                    <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[#272320]">
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="trending-up" class="w-4 h-4 text-amber-500"></i>
                                <span>HPP & Margin per Menu</span>
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-[#2D2A26] bg-[#181716] text-[#A8A29E] uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3.5">Produk</th>
                                        <th class="px-5 py-3.5 text-center">Ukuran</th>
                                        <th class="px-5 py-3.5 text-right">Harga Jual</th>
                                        <th class="px-5 py-3.5 text-right">HPP</th>
                                        <th class="px-5 py-3.5 text-right">Margin Rp</th>
                                        <th class="px-5 py-3.5 text-right">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#272320]">
                                    @forelse($hppMargin as $item)
                                        <tr class="hover:bg-[#1E1C1A] transition">
                                            <td class="px-5 py-3.5 font-bold text-white">{{ $item->product_name }}</td>
                                            <td class="px-5 py-3.5 text-center font-mono">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-[#141312] border border-[#2D2A26] text-stone-300">
                                                    {{ strtoupper($item->size_variant) }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-white">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono text-stone-300">Rp {{ number_format($item->hpp, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-400">Rp {{ number_format($item->margin_rp, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-400">{{ number_format($item->margin_pct, 2, ',', '.') }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-6 text-center text-[#A8A29E]">Tidak ada produk dengan resep terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl border border-[#2D2A26] bg-[#141210] overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-[#272320]">
                            <h4 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4 text-amber-500"></i>
                                <span>Rekap Sesi Kasir</span>
                            </h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs text-stone-200">
                                <thead class="border-b border-[#2D2A26] bg-[#181716] text-[#A8A29E] uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="px-5 py-3.5">Sesi</th>
                                        <th class="px-5 py-3.5">Kasir</th>
                                        <th class="px-5 py-3.5">Dibuka</th>
                                        <th class="px-5 py-3.5">Ditutup</th>
                                        <th class="px-5 py-3.5 text-right">Total Penjualan</th>
                                        <th class="px-5 py-3.5 text-center">Jumlah Transaksi</th>
                                        <th class="px-5 py-3.5 text-right">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#272320]">
                                    @forelse($cashierSessions as $session)
                                        <tr class="hover:bg-[#1E1C1A] transition">
                                            <td class="px-5 py-3.5 font-mono text-amber-400 font-bold">#{{ $session->id }}</td>
                                            <td class="px-5 py-3.5 font-bold text-white">{{ $session->user_name }}</td>
                                            <td class="px-5 py-3.5 font-mono text-[#A8A29E] text-[11px]">{{ optional($session->opened_at)->format('d M Y H:i') }}</td>
                                            <td class="px-5 py-3.5 font-mono text-[#A8A29E] text-[11px]">{{ optional($session->closed_at)->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold text-white">Rp {{ number_format($session->total_sales, 0, ',', '.') }}</td>
                                            <td class="px-5 py-3.5 text-center font-mono font-bold text-amber-400">{{ $session->orders_count }}</td>
                                            <td class="px-5 py-3.5 text-right font-mono font-bold {{ $session->difference < 0 ? 'text-rose-400' : ($session->difference > 0 ? 'text-emerald-400' : 'text-stone-300') }}">
                                                Rp {{ number_format($session->difference, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-5 py-6 text-center text-[#A8A29E]">Tidak ada sesi kasir pada periode ini.</td>
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
