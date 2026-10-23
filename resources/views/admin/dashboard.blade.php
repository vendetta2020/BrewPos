@extends('layouts.admin')

@section('title', 'Dashboard - BrewPos')
@section('page_title', 'Ringkasan Bisnis & Operasional')

@section('content')
<div class="space-y-6">
    <!-- Top 4 Operational KPI Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Hari Ini -->
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Pendapatan Hari Ini</span>
                <i data-lucide="wallet" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-white tracking-tight tabular-nums">
                    Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}
                </div>
                <div class="flex items-center gap-1.5 mt-1">
                    <span class="inline-flex items-center gap-1 text-[10px] font-mono font-medium text-emerald-400">
                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                        <span>Pesanan Terbayar</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Total Transaksi</span>
                <i data-lucide="receipt" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-white tracking-tight tabular-nums">
                    {{ number_format($todayCount ?? 0) }}
                </div>
                <p class="text-[11px] text-stone-400 font-mono mt-1">
                    AOV: <span class="text-stone-300">Rp {{ number_format($avgOrder ?? 0, 0, ',', '.') }}</span>
                </p>
            </div>
        </div>

        <!-- Stok Kritis -->
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Stok Kritis</span>
                <i data-lucide="alert-triangle" class="w-4 h-4 {{ $criticalMaterials->count() > 0 ? 'text-rose-400' : 'text-stone-400' }}"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono tracking-tight tabular-nums {{ $criticalMaterials->count() > 0 ? 'text-rose-400' : 'text-emerald-400' }}">
                    {{ $criticalMaterials->count() }} <span class="text-xs font-sans text-stone-400 font-normal">Bahan</span>
                </div>
                <p class="text-[11px] text-stone-400 mt-1">
                    {{ $criticalMaterials->count() > 0 ? 'Mencapai batas minimum' : 'Semua bahan baku aman' }}
                </p>
            </div>
        </div>

        <!-- Sesi Kasir Aktif -->
        <div class="stat-tile flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-stone-400">Kasir Aktif</span>
                <i data-lucide="users" class="w-4 h-4 text-stone-400"></i>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-bold font-mono text-white tracking-tight tabular-nums">
                    {{ $activeSessions->count() }} <span class="text-xs font-sans text-stone-400 font-normal">Shift</span>
                </div>
                <p class="text-[11px] text-stone-400 mt-1">
                    {{ $activeSessions->count() > 0 ? 'Sesi kasir sedang buka' : 'Belum ada kasir bertugas' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Alert Stok Kritis Banner (Jika ada bahan baku menipis) -->
    @if($criticalMaterials->isNotEmpty())
        <div class="surface-card p-4 border-rose-500/30 bg-rose-500/5 space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-rose-300 text-xs">Peringatan Bahan Baku Kritis</h4>
                        <p class="text-[11px] text-stone-400 mt-0.5">Terdapat {{ $criticalMaterials->count() }} bahan baku yang sudah mencapai atau di bawah batas minimum.</p>
                    </div>
                </div>
                <a href="{{ route('admin.raw-materials.index') }}" class="btn btn-sm btn-destructive shrink-0">
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    <span>Tinjau Bahan Baku</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 pt-2 border-t border-[#2A2724]">
                @foreach($criticalMaterials->take(6) as $item)
                    <div class="surface-card-inset p-2.5 flex justify-between items-center text-xs">
                        <span class="font-medium text-stone-200 truncate pr-2">{{ $item->name }}</span>
                        <span class="badge badge-danger shrink-0 font-mono">
                            {{ $item->stock_qty }} {{ $item->unit }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2 Column Analytics & Live Shift Context -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Menu Terlaris Hari Ini -->
        <div class="surface-card p-4 space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-[#2A2724]">
                <div class="flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-amber-500"></i>
                    <h3 class="text-xs font-bold text-white tracking-wide uppercase">Menu Terlaris Hari Ini</h3>
                </div>
                <span class="badge badge-neutral">Top 5 Menu</span>
            </div>

            @if($topProducts->isEmpty())
                <div class="py-10 flex flex-col items-center justify-center text-center space-y-1.5 border border-dashed border-[#2A2724] rounded-lg">
                    <i data-lucide="coffee" class="w-6 h-6 text-stone-600"></i>
                    <p class="text-xs text-stone-400 font-medium">Belum ada transaksi selesai hari ini</p>
                    <p class="text-[11px] text-stone-500">Pesanan kasir yang dibayar akan muncul di sini</p>
                </div>
            @else
                <div class="space-y-1.5">
                    @foreach($topProducts as $idx => $item)
                        <div class="flex items-center justify-between p-2.5 rounded-lg hover:bg-[#1E1C1A] border border-[#22201D] transition-colors">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="w-5 text-center font-mono text-xs font-bold text-stone-500">
                                    {{ $idx + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-xs text-stone-200 truncate">
                                        {{ $item->product->name ?? 'Produk' }}
                                    </p>
                                    <p class="text-[10px] text-stone-400 font-mono">
                                        {{ $item->total_qty }} porsi terjual
                                    </p>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-xs text-stone-200 tabular-nums shrink-0 ml-3">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sesi Shift Kasir Aktif -->
        <div class="surface-card p-4 space-y-3">
            <div class="flex items-center justify-between pb-2.5 border-b border-[#2A2724]">
                <div class="flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-amber-500"></i>
                    <h3 class="text-xs font-bold text-white tracking-wide uppercase">Sesi Shift Kasir Aktif</h3>
                </div>
                <span class="badge {{ $activeSessions->count() > 0 ? 'badge-ready' : 'badge-neutral' }}">
                    {{ $activeSessions->count() }} Shift Buka
                </span>
            </div>

            @if($activeSessions->isEmpty())
                <div class="py-10 flex flex-col items-center justify-center text-center space-y-1.5 border border-dashed border-[#2A2724] rounded-lg">
                    <i data-lucide="clock" class="w-6 h-6 text-stone-600"></i>
                    <p class="text-xs text-stone-400 font-medium">Belum ada kasir yang membuka sesi shift</p>
                    <p class="text-[11px] text-stone-500">Buka sesi di layar POS untuk mulai bertransaksi</p>
                </div>
            @else
                <div class="space-y-1.5">
                    @foreach($activeSessions as $session)
                        <div class="flex items-center justify-between p-2.5 rounded-lg border border-[#22201D] bg-[#141312]">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-md bg-[#22201E] border border-[#2A2724] text-stone-300 flex items-center justify-center font-bold text-xs font-mono shrink-0">
                                    {{ substr($session->user->name ?? 'K', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-xs text-white truncate">{{ $session->user->name ?? 'Kasir' }}</p>
                                    <p class="text-[10px] text-stone-400 font-mono">
                                        Shift #{{ $session->id }} • Buka: {{ $session->opened_at ? $session->opened_at->format('H:i') : '-' }} • Modal: Rp {{ number_format($session->opening_balance, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <span class="badge badge-ready shrink-0 ml-3">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>TERBUKA</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Action Navigation Hub -->
    <div class="surface-card p-4 space-y-2.5">
        <div class="flex items-center gap-1.5">
            <h3 class="text-xs font-bold text-stone-400 uppercase tracking-wider">Akses Cepat Modul</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
            <a href="{{ route('admin.products.index') }}" class="p-3 surface-card-inset hover:border-[#383430] hover:bg-[#1C1A18] transition-colors flex items-center gap-2.5 group">
                <i data-lucide="package" class="w-4 h-4 text-stone-400 group-hover:text-amber-400 shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-stone-200 group-hover:text-white truncate">Produk</p>
                    <p class="text-[10px] text-stone-500 truncate">Katalog & Barcode</p>
                </div>
            </a>
            <a href="{{ route('admin.raw-materials.index') }}" class="p-3 surface-card-inset hover:border-[#383430] hover:bg-[#1C1A18] transition-colors flex items-center gap-2.5 group">
                <i data-lucide="boxes" class="w-4 h-4 text-stone-400 group-hover:text-amber-400 shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-stone-200 group-hover:text-white truncate">Bahan Baku</p>
                    <p class="text-[10px] text-stone-500 truncate">Stok & Nilai</p>
                </div>
            </a>
            <a href="{{ route('admin.recipes.index') }}" class="p-3 surface-card-inset hover:border-[#383430] hover:bg-[#1C1A18] transition-colors flex items-center gap-2.5 group">
                <i data-lucide="book-open" class="w-4 h-4 text-stone-400 group-hover:text-amber-400 shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-stone-200 group-hover:text-white truncate">Resep & HPP</p>
                    <p class="text-[10px] text-stone-500 truncate">Formulasi Menu</p>
                </div>
            </a>
            <a href="{{ route('admin.reports.sales') }}" class="p-3 surface-card-inset hover:border-[#383430] hover:bg-[#1C1A18] transition-colors flex items-center gap-2.5 group">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-stone-400 group-hover:text-amber-400 shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-stone-200 group-hover:text-white truncate">Laporan</p>
                    <p class="text-[10px] text-stone-500 truncate">Penjualan & Ekspor</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
