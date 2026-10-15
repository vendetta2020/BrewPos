@extends('layouts.admin')

@section('title', 'Laporan & Analitik')
@section('page_title', 'Laporan & Analitik')

@section('content')
    <div class="space-y-6">
        <form method="GET" class="grid gap-4 md:grid-cols-[auto_260px]">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-600">Periode</label>
                    <select name="period" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @foreach(['today' => 'Hari Ini', 'this_week' => 'Minggu Ini', 'this_month' => 'Bulan Ini', 'last_30_days' => '30 Hari Terakhir', 'custom' => 'Kustom'] as $value => $label)
                            <option value="{{ $value }}" {{ $filters['period'] === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Mulai</label>
                    <input type="date" name="start" value="{{ optional($filters['start'])->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600">Sampai</label>
                    <input type="date" name="end" value="{{ optional($filters['end'])->format('Y-m-d') }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="flex items-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Terapkan</button>
                </div>
            </div>
        </form>

        <div class="bg-white shadow rounded-xl border border-gray-200">
            <div class="border-b border-gray-200 p-4">
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
                        <a href="{{ route('admin.reports.sales', array_merge(request()->only(['period', 'start', 'end']), ['tab' => $key])) }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ $activeTab === $key ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $tabs[$activeTab] ?? 'Penjualan' }}</h3>
                        <p class="text-sm text-gray-500">Periode: {{ $filters['start']->format('d M Y') }} – {{ $filters['end']->format('d M Y') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if(in_array($activeTab, ['sales']))
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel'])) }}" class="rounded-lg border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50">Excel</a>
                            <a href="{{ route('admin.reports.sales.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf'])) }}" class="rounded-lg border border-gray-800 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-100">PDF</a>
                        @else
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'excel', 'section' => $activeTab])) }}" class="rounded-lg border border-blue-600 px-4 py-2 text-sm font-semibold text-blue-600 hover:bg-blue-50">Excel</a>
                            <a href="{{ route('admin.reports.inventory.export', array_merge(request()->only(['period', 'start', 'end']), ['format' => 'pdf', 'section' => $activeTab])) }}" class="rounded-lg border border-gray-800 px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-gray-100">PDF</a>
                        @endif
                    </div>
                </div>

                @if($activeTab === 'sales')
                    <div class="grid gap-4 lg:grid-cols-4 mb-6">
                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Pendapatan</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format($salesSummary['total_revenue'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Transaksi</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $salesSummary['transaction_count'] }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">AOV</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">Rp {{ number_format($salesSummary['average_order_value'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-slate-50 p-5">
                            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">Stok Kritis</p>
                            <p class="mt-4 text-3xl font-semibold text-slate-900">{{ $salesSummary['critical_stock_count'] }}</p>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">Produk Terlaris</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="border-b border-gray-200 bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3">Terjual</th>
                                        <th class="px-4 py-3">Pendapatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProducts as $item)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-4">{{ $item->product->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-4 py-4">{{ $item->quantity_sold }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->revenue, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">Belum ada data penjualan pada periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'raw_material')
                    <div class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">Pemakaian Bahan Baku</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="border-b border-gray-200 bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3">Bahan Baku</th>
                                        <th class="px-4 py-3">Qty Digunakan</th>
                                        <th class="px-4 py-3">Total Biaya</th>
                                        <th class="px-4 py-3">Biaya / Unit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rawMaterialUsage as $item)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-4">{{ $item->raw_material->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-4 py-4">{{ number_format($item->qty_used, 2, ',', '.') }} {{ $item->raw_material->unit ?? '' }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada pemakaian bahan selama periode ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @elseif($activeTab === 'hpp_margin')
                    <div class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">HPP & Margin per Menu</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="border-b border-gray-200 bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3">Produk</th>
                                        <th class="px-4 py-3">Ukuran</th>
                                        <th class="px-4 py-3">Harga Jual</th>
                                        <th class="px-4 py-3">HPP</th>
                                        <th class="px-4 py-3">Margin Rp</th>
                                        <th class="px-4 py-3">Margin %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hppMargin as $item)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-4">{{ $item->product_name }}</td>
                                            <td class="px-4 py-4">{{ strtoupper($item->size_variant) }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->sale_price, 0, ',', '.') }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->hpp, 0, ',', '.') }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($item->margin_rp, 0, ',', '.') }}</td>
                                            <td class="px-4 py-4">{{ number_format($item->margin_pct, 2, ',', '.') }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada produk dengan resep terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="rounded-3xl border border-gray-200 bg-white p-6">
                        <h4 class="text-base font-semibold text-gray-900 mb-4">Rekap Sesi Kasir</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="border-b border-gray-200 bg-gray-50 text-gray-700">
                                    <tr>
                                        <th class="px-4 py-3">Sesi</th>
                                        <th class="px-4 py-3">Kasir</th>
                                        <th class="px-4 py-3">Dibuka</th>
                                        <th class="px-4 py-3">Ditutup</th>
                                        <th class="px-4 py-3">Total Penjualan</th>
                                        <th class="px-4 py-3">Jumlah Transaksi</th>
                                        <th class="px-4 py-3">Selisih</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cashierSessions as $session)
                                        <tr class="border-b border-gray-100">
                                            <td class="px-4 py-4">#{{ $session->id }}</td>
                                            <td class="px-4 py-4">{{ $session->user_name }}</td>
                                            <td class="px-4 py-4">{{ optional($session->opened_at)->format('d M Y H:i') }}</td>
                                            <td class="px-4 py-4">{{ optional($session->closed_at)->format('d M Y H:i') ?? '-' }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($session->total_sales, 0, ',', '.') }}</td>
                                            <td class="px-4 py-4">{{ $session->orders_count }}</td>
                                            <td class="px-4 py-4">Rp {{ number_format($session->difference, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada sesi kasir pada periode ini.</td>
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
