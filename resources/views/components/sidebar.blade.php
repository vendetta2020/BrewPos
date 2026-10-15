<aside class="w-64 bg-[#141210] text-[#F3F0EA] flex flex-col h-full border-r border-[#272320] select-none">
    <!-- Brand Header -->
    <div class="px-5 py-4 border-b border-[#272320] flex items-center justify-between shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-600/20 border border-amber-500/30 flex items-center justify-center text-amber-500 font-bold shrink-0">
                <i data-lucide="coffee" class="w-5 h-5"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold tracking-tight leading-none text-white flex items-center gap-1.5">
                    BrewPOS <span class="text-[9px] uppercase font-mono tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Admin</span>
                </h1>
                <p class="text-[11px] text-stone-400 mt-0.5">Sistem Manajemen POS</p>
            </div>
        </div>
        <!-- Close button on mobile -->
        <button 
            @click="sidebarOpen = false" 
            class="md:hidden text-stone-400 hover:text-white p-1 rounded-lg hover:bg-stone-800 transition"
            aria-label="Tutup Navigasi"
        >
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard -->
        <a 
            href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('dashboard') ? 'bg-amber-600/15 text-amber-400 border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
        >
            <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-400 shrink-0"></i>
            <span>Dashboard</span>
        </a>

        <!-- Menu Section -->
        <div class="pt-4">
            <p class="px-3.5 pb-1.5 text-[10px] font-bold text-stone-500 uppercase tracking-wider">Katalog & Menu</p>
            <div class="space-y-0.5">
                <a 
                    href="{{ route('admin.products.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.products*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="package" class="w-4 h-4 shrink-0"></i>
                    <span>Manajemen Produk</span>
                </a>
                <a 
                    href="{{ route('admin.categories.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.categories*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="tag" class="w-4 h-4 shrink-0"></i>
                    <span>Kategori Produk</span>
                </a>
            </div>
        </div>

        <!-- Inventory Section -->
        <div class="pt-4">
            <p class="px-3.5 pb-1.5 text-[10px] font-bold text-stone-500 uppercase tracking-wider">Bahan & Inventori</p>
            <div class="space-y-0.5">
                <a 
                    href="{{ route('admin.raw-materials.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.raw-materials*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="boxes" class="w-4 h-4 shrink-0"></i>
                    <span>Bahan Baku</span>
                </a>
                <a 
                    href="{{ route('admin.recipes.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.recipes*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="book-open" class="w-4 h-4 shrink-0"></i>
                    <span>Manajemen Resep</span>
                </a>
                <a 
                    href="{{ route('admin.restocks.create') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.restocks*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="arrow-down-to-dot" class="w-4 h-4 shrink-0"></i>
                    <span>Catat Restock</span>
                </a>
                <a 
                    href="{{ route('admin.inventory.movements') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.inventory.movements') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="history" class="w-4 h-4 shrink-0"></i>
                    <span>Log Mutasi Stok</span>
                </a>
            </div>
        </div>

        <!-- Transactions Section -->
        <div class="pt-4">
            <p class="px-3.5 pb-1.5 text-[10px] font-bold text-stone-500 uppercase tracking-wider">Operasional & Kasir</p>
            <div class="space-y-0.5">
                <a 
                    href="{{ route('admin.orders.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.orders*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="receipt" class="w-4 h-4 shrink-0"></i>
                    <span>Riwayat Pesanan</span>
                </a>
                <a 
                    href="{{ route('pos') }}"
                    target="_blank"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]"
                >
                    <i data-lucide="monitor" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                    <span>Layar Kasir (POS)</span>
                    <i data-lucide="external-link" class="w-3 h-3 ml-auto opacity-50"></i>
                </a>
                <a 
                    href="{{ route('kds') }}"
                    target="_blank"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]"
                >
                    <i data-lucide="chef-hat" class="w-4 h-4 text-amber-400 shrink-0"></i>
                    <span>Kitchen Display (KDS)</span>
                    <i data-lucide="external-link" class="w-3 h-3 ml-auto opacity-50"></i>
                </a>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="pt-4">
            <p class="px-3.5 pb-1.5 text-[10px] font-bold text-stone-500 uppercase tracking-wider">Laporan & Analitik</p>
            <div class="space-y-0.5">
                <a 
                    href="{{ route('admin.reports.sales') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.reports.sales') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="trending-up" class="w-4 h-4 shrink-0"></i>
                    <span>Laporan Penjualan</span>
                </a>
                <a 
                    href="{{ route('admin.reports.inventory') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.reports.inventory') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="bar-chart-3" class="w-4 h-4 shrink-0"></i>
                    <span>Laporan Bahan Baku</span>
                </a>
            </div>
        </div>

        <!-- Settings Section -->
        <div class="pt-4 pb-2">
            <p class="px-3.5 pb-1.5 text-[10px] font-bold text-stone-500 uppercase tracking-wider">Sistem</p>
            <div class="space-y-0.5">
                <a 
                    href="{{ route('admin.users.index') }}"
                    class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('admin.users*') ? 'bg-amber-600/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-stone-400 hover:text-stone-200 hover:bg-[#201D1A]' }}"
                >
                    <i data-lucide="users" class="w-4 h-4 shrink-0"></i>
                    <span>Manajemen Pengguna</span>
                </a>
            </div>
        </div>
    </nav>
</aside>
