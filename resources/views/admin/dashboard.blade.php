@extends('layouts.admin')

@section('title', 'Dashboard - BrewPos')
@section('page_title', 'Dashboard Ringkasan Bisnis')

@section('content')
    <!-- Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Revenue -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Pendapatan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-1">Status: Selesai</p>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="wallet" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-800 mt-2">{{ number_format($todayCount ?? 0) }}</p>
                    <p class="text-xs text-gray-400 mt-1">AOV: Rp {{ number_format($avgOrder ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Stok Kritis</p>
                    <p class="text-2xl font-bold text-red-600 mt-2">{{ $criticalMaterials->count() }} Bahan</p>
                    <p class="text-xs text-red-400 mt-1 font-medium">Batas stok minimum</p>
                </div>
                <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </div>
            </div>
        </div>

        <!-- Active Cashiers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-semibold">Kasir Aktif</p>
                    <p class="text-2xl font-bold text-purple-600 mt-2">{{ $activeSessions->count() }} Shift</p>
                    <p class="text-xs text-purple-400 mt-1 font-medium">Sesi berjalan</p>
                </div>
                <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Stok Kritis Banner (jika ada) -->
    @if($criticalMaterials->isNotEmpty())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                        <i data-lucide="bell-ring" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-red-900">Peringatan Bahan Baku Kritis</h4>
                        <p class="text-xs text-red-700">Terdapat {{ $criticalMaterials->count() }} bahan baku yang sudah mencapai atau di bawah stok minimum.</p>
                    </div>
                </div>
                <a href="{{ route('admin.raw-materials.index') }}" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition">
                    Restock Sekarang
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-2 border-t border-red-200/60">
                @foreach($criticalMaterials->take(6) as $item)
                    <div class="bg-white p-3 rounded-xl border border-red-100 flex justify-between items-center text-xs">
                        <span class="font-semibold text-gray-800">{{ $item->name }}</span>
                        <span class="font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full border border-red-200">
                            {{ $item->stock_qty }} {{ $item->unit }} (Min: {{ $item->min_stock_qty }})
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Charts & Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Best Sellers -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-amber-500"></i>
                <span>Menu Terlaris Hari Ini</span>
            </h3>
            @if($topProducts->isEmpty())
                <p class="text-center text-gray-400 text-sm py-12">Belum ada transaksi selesai hari ini</p>
            @else
                <div class="space-y-3">
                    @foreach($topProducts as $item)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-50 text-blue-600 font-bold rounded-lg flex items-center justify-center text-sm">
                                    <i data-lucide="coffee" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-sm text-gray-800">{{ $item->product->name ?? 'Produk' }}</p>
                                    <p class="text-xs text-gray-500">Terjual: {{ $item->total_qty }} porsi</p>
                                </div>
                            </div>
                            <span class="font-bold text-sm text-blue-600">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Cashier Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="users" class="w-5 h-5 text-purple-500"></i>
                <span>Sesi Kasir Aktif</span>
            </h3>
            @if($activeSessions->isEmpty())
                <p class="text-center text-gray-400 text-sm py-12">Belum ada kasir yang membuka sesi hari ini</p>
            @else
                <div class="space-y-3">
                    @foreach($activeSessions as $session)
                        <div class="flex justify-between items-center p-3 bg-green-50/50 rounded-xl border border-green-100">
                            <div>
                                <p class="font-bold text-sm text-gray-800">{{ $session->user->name ?? 'Kasir' }}</p>
                                <p class="text-xs text-gray-500">Buka: {{ $session->opened_at->format('H:i') }} | Modal: Rp {{ number_format($session->opening_balance, 0, ',', '.') }}</p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 bg-green-100 text-green-700 rounded-full">
                                ● TERBUKA
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i data-lucide="zap" class="w-5 h-5 text-amber-500"></i>
            <span>Aksi Cepat</span>
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.products.index') }}" class="p-4 bg-blue-50/60 hover:bg-blue-100/80 rounded-2xl text-center border border-blue-100 transition flex flex-col items-center">
                <i data-lucide="package" class="w-7 h-7 mb-2 text-blue-600"></i>
                <p class="text-sm font-bold text-gray-800">Manajemen Produk</p>
            </a>
            <a href="{{ route('admin.raw-materials.index') }}" class="p-4 bg-green-50/60 hover:bg-green-100/80 rounded-2xl text-center border border-green-100 transition flex flex-col items-center">
                <i data-lucide="boxes" class="w-7 h-7 mb-2 text-green-600"></i>
                <p class="text-sm font-bold text-gray-800">Bahan Baku</p>
            </a>
            <a href="{{ route('admin.recipes.index') }}" class="p-4 bg-purple-50/60 hover:bg-purple-100/80 rounded-2xl text-center border border-purple-100 transition flex flex-col items-center">
                <i data-lucide="book-open" class="w-7 h-7 mb-2 text-purple-600"></i>
                <p class="text-sm font-bold text-gray-800">Manajemen Resep</p>
            </a>
            <a href="{{ route('admin.reports.sales') }}" class="p-4 bg-orange-50/60 hover:bg-orange-100/80 rounded-2xl text-center border border-orange-100 transition flex flex-col items-center">
                <i data-lucide="trending-up" class="w-7 h-7 mb-2 text-orange-600"></i>
                <p class="text-sm font-bold text-gray-800">Laporan Penjualan</p>
            </a>
        </div>
    </div>
@endsection
