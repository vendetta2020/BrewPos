@extends('layouts.admin')

@section('title', 'Dashboard - BrewPos')
@section('page_title', 'Ringkasan Bisnis & Operasional')

@section('content')
<div class="space-y-6">
    <!-- Top 4 KPI Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendapatan Hari Ini -->
        <div class="bg-[#181716] border border-[#2D2A26] hover:border-amber-500/40 rounded-2xl p-5 transition shadow-lg flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-[#A8A29E] uppercase tracking-wider">Pendapatan Hari Ini</p>
                <p class="text-2xl font-black font-mono text-white tracking-tight">
                    Rp {{ number_format($todaySales ?? 0, 0, ',', '.') }}
                </p>
                <div class="flex items-center gap-1.5 pt-0.5">
                    <span class="inline-flex items-center gap-1 text-[10px] font-mono px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                        Status: Selesai
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="wallet" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Total Transaksi -->
        <div class="bg-[#181716] border border-[#2D2A26] hover:border-amber-500/40 rounded-2xl p-5 transition shadow-lg flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-[#A8A29E] uppercase tracking-wider">Total Transaksi</p>
                <p class="text-2xl font-black font-mono text-white tracking-tight">
                    {{ number_format($todayCount ?? 0) }}
                </p>
                <p class="text-[11px] text-[#A8A29E] font-mono">
                    AOV: <span class="text-stone-300">Rp {{ number_format($avgOrder ?? 0, 0, ',', '.') }}</span>
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Stok Kritis -->
        <div class="bg-[#181716] border border-[#2D2A26] hover:border-rose-500/40 rounded-2xl p-5 transition shadow-lg flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-[#A8A29E] uppercase tracking-wider">Stok Kritis</p>
                <p class="text-2xl font-black font-mono {{ $criticalMaterials->count() > 0 ? 'text-rose-400' : 'text-emerald-400' }} tracking-tight">
                    {{ $criticalMaterials->count() }} <span class="text-xs font-sans text-stone-400 font-normal">Bahan</span>
                </p>
                <p class="text-[11px] text-stone-400 font-medium">Batas stok minimum</p>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $criticalMaterials->count() > 0 ? 'bg-rose-500/10 border border-rose-500/25 text-rose-400' : 'bg-stone-800 border border-stone-700 text-stone-400' }} flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Kasir Aktif -->
        <div class="bg-[#181716] border border-[#2D2A26] hover:border-amber-500/40 rounded-2xl p-5 transition shadow-lg flex items-center justify-between group">
            <div class="space-y-1">
                <p class="text-xs font-semibold text-[#A8A29E] uppercase tracking-wider">Kasir Aktif</p>
                <p class="text-2xl font-black font-mono text-purple-400 tracking-tight">
                    {{ $activeSessions->count() }} <span class="text-xs font-sans text-stone-400 font-normal">Shift</span>
                </p>
                <p class="text-[11px] text-stone-400 font-medium">Sesi berjalan</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- Alert Stok Kritis Banner (Jika ada bahan baku menipis) -->
    @if($criticalMaterials->isNotEmpty())
        <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-5 shadow-lg space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/20 border border-rose-500/30 text-rose-400 flex items-center justify-center shrink-0">
                        <i data-lucide="bell-ring" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-rose-300 text-sm">Peringatan Bahan Baku Kritis</h4>
                        <p class="text-xs text-rose-300/80">Terdapat {{ $criticalMaterials->count() }} bahan baku yang sudah mencapai atau di bawah batas minimum.</p>
                    </div>
                </div>
                <a href="{{ route('admin.raw-materials.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold rounded-xl transition shadow-md shadow-rose-600/20 shrink-0">
                    <i data-lucide="arrow-down-to-dot" class="w-4 h-4"></i>
                    <span>Restock Sekarang</span>
                </a>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2.5 pt-2 border-t border-rose-500/20">
                @foreach($criticalMaterials->take(6) as $item)
                    <div class="bg-[#141210] p-3 rounded-xl border border-rose-500/20 flex justify-between items-center text-xs">
                        <span class="font-semibold text-stone-200">{{ $item->name }}</span>
                        <span class="font-mono font-bold text-rose-400 bg-rose-500/10 px-2 py-0.5 rounded-lg border border-rose-500/20">
                            {{ $item->stock_qty }} {{ $item->unit }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2 Column Analytics & Live Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Menu Terlaris Hari Ini -->
        <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#272320]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-tight">Menu Terlaris Hari Ini</h3>
                        <p class="text-[10px] text-stone-400">Peringkat kontribusi pesanan & omset</p>
                    </div>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#141312] text-amber-400 border border-[#2D2A26]">
                    Top 5 Menu
                </span>
            </div>

            @if($topProducts->isEmpty())
                <div class="py-12 flex flex-col items-center justify-center text-center space-y-2 border border-dashed border-[#272320] rounded-xl bg-[#141210]/40">
                    <div class="w-10 h-10 rounded-full bg-[#181716] border border-[#272320] flex items-center justify-center text-stone-500">
                        <i data-lucide="coffee" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs text-stone-400 font-medium">Belum ada transaksi selesai hari ini</p>
                    <p class="text-[10px] text-stone-600">Pesanan kasir yang dibayar akan muncul di sini</p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($topProducts as $idx => $item)
                        <div class="flex items-center justify-between p-3 bg-[#141210] hover:bg-[#1E1C1A] rounded-xl border border-[#272320] transition group">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 font-mono font-bold text-xs flex items-center justify-center">
                                    #{{ $idx + 1 }}
                                </span>
                                <div>
                                    <p class="font-bold text-xs text-white group-hover:text-amber-300 transition-colors">
                                        {{ $item->product->name ?? 'Produk' }}
                                    </p>
                                    <p class="text-[10px] text-stone-400 font-mono">
                                        Terjual: <span class="text-stone-300 font-semibold">{{ $item->total_qty }}</span> porsi
                                    </p>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-xs text-amber-400">
                                Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Sesi Kasir Aktif -->
        <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl p-5 shadow-lg space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-[#272320]">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white leading-tight">Sesi Shift Kasir Aktif</h3>
                        <p class="text-[10px] text-stone-400">Kasir yang sedang melayani transaksi</p>
                    </div>
                </div>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#141210] text-purple-400 border border-[#2D2A26]">
                    {{ $activeSessions->count() }} Kasir Buka
                </span>
            </div>

            @if($activeSessions->isEmpty())
                <div class="py-12 flex flex-col items-center justify-center text-center space-y-2 border border-dashed border-[#272320] rounded-xl bg-[#141210]/40">
                    <div class="w-10 h-10 rounded-full bg-[#181716] border border-[#272320] flex items-center justify-center text-stone-500">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <p class="text-xs text-stone-400 font-medium">Belum ada kasir yang membuka sesi shift</p>
                    <p class="text-[10px] text-stone-600">Buka sesi di layar POS untuk mulai bertransaksi</p>
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($activeSessions as $session)
                        <div class="flex items-center justify-between p-3.5 bg-[#141210] hover:bg-[#1E1C1A] rounded-xl border border-[#272320] transition">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs font-mono">
                                    {{ substr($session->user->name ?? 'K', 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-xs text-white">{{ $session->user->name ?? 'Kasir' }}</p>
                                    <p class="text-[10px] text-stone-400 font-mono">
                                        Shift #{{ $session->id }} • Jam: {{ $session->opened_at ? $session->opened_at->format('H:i') : '-' }} • Modal: Rp {{ number_format($session->opening_balance, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-mono px-2.5 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/25 rounded-lg font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                TERBUKA
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Action Navigation Hub -->
    <div class="bg-[#181716] border border-[#2D2A26] rounded-2xl p-5 shadow-lg space-y-3.5">
        <div class="flex items-center gap-2">
            <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
            <h3 class="text-xs font-bold text-stone-300 uppercase tracking-wider">Aksi Cepat Manajemen</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="{{ route('admin.products.index') }}" class="p-4 bg-[#141210] hover:bg-[#201D1A] rounded-xl text-center border border-[#272320] hover:border-amber-500/40 transition flex flex-col items-center group">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i data-lucide="package" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-white group-hover:text-amber-300">Manajemen Produk</p>
                <p class="text-[10px] text-stone-500 mt-0.5">Katalog & Barcode</p>
            </a>
            <a href="{{ route('admin.raw-materials.index') }}" class="p-4 bg-[#141210] hover:bg-[#201D1A] rounded-xl text-center border border-[#272320] hover:border-emerald-500/40 transition flex flex-col items-center group">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i data-lucide="boxes" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-white group-hover:text-emerald-300">Bahan Baku</p>
                <p class="text-[10px] text-stone-500 mt-0.5">Stok & Penyesuaian</p>
            </a>
            <a href="{{ route('admin.recipes.index') }}" class="p-4 bg-[#141210] hover:bg-[#201D1A] rounded-xl text-center border border-[#272320] hover:border-purple-500/40 transition flex flex-col items-center group">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-white group-hover:text-purple-300">Manajemen Resep</p>
                <p class="text-[10px] text-stone-500 mt-0.5">Bill of Materials</p>
            </a>
            <a href="{{ route('admin.reports.sales') }}" class="p-4 bg-[#141210] hover:bg-[#201D1A] rounded-xl text-center border border-[#272320] hover:border-amber-500/40 transition flex flex-col items-center group">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i data-lucide="trending-up" class="w-5 h-5"></i>
                </div>
                <p class="text-xs font-bold text-white group-hover:text-amber-300">Laporan Penjualan</p>
                <p class="text-[10px] text-stone-500 mt-0.5">Omset & Ekspor</p>
            </a>
        </div>
    </div>
</div>
@endsection
