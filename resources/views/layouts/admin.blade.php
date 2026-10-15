<!DOCTYPE html>
<html lang="id" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] antialiased font-sans h-full selection:bg-amber-500 selection:text-black">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen w-full overflow-hidden bg-[#0E0D0D]">
        <!-- Desktop Static Sidebar (Always visible on md+, guaranteed in flex flow without overlap) -->
        <div class="hidden md:flex md:w-64 md:shrink-0 md:flex-col h-full bg-[#141210] border-r border-[#272320] z-30">
            @include('components.sidebar')
        </div>

        <!-- Mobile Drawer Overlay (Only renders over page on small screens when sidebarOpen is true) -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            class="fixed inset-0 z-50 flex md:hidden"
        >
            <!-- Mobile Dark Backdrop -->
            <div 
                @click="sidebarOpen = false" 
                class="fixed inset-0 bg-black/80 backdrop-blur-xs transition-opacity duration-200"
            ></div>
            <!-- Drawer Content Panel -->
            <div class="relative flex-1 flex flex-col max-w-xs w-full bg-[#141210] border-r border-[#272320] z-50">
                @include('components.sidebar')
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-[#0E0D0D]">
            <!-- Top Navigation Bar -->
            <header class="h-16 bg-[#141210]/90 backdrop-blur-md border-b border-[#272320] px-4 sm:px-6 flex items-center justify-between shrink-0 sticky top-0 z-20">
                <!-- Mobile Hamburger & Page Title -->
                <div class="flex items-center gap-3.5">
                    <button 
                        @click="sidebarOpen = true" 
                        class="md:hidden p-2 text-stone-400 hover:text-white hover:bg-[#201D1A] rounded-xl transition border border-[#2D2A26]"
                        aria-label="Buka Navigasi Menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-white tracking-tight leading-tight flex items-center gap-2">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <p class="text-[10px] text-stone-400 hidden sm:block font-medium">Panel Pengelolaan Operasional & Inventori</p>
                    </div>
                </div>

                <!-- Right Header Actions & Profile -->
                <div class="flex items-center gap-3">
                    <!-- Quick POS Link -->
                    <a 
                        href="{{ route('pos') }}" 
                        target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 text-xs font-semibold rounded-xl border border-amber-500/25 transition-all shadow-xs"
                        title="Buka Kasir POS di Tab Baru"
                    >
                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Buka POS</span>
                    </a>

                    <!-- Quick KDS Link -->
                    <a 
                        href="{{ route('kds') }}" 
                        target="_blank"
                        class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 text-xs font-semibold rounded-xl border border-purple-500/25 transition-all shadow-xs"
                        title="Buka Kitchen Display di Tab Baru"
                    >
                        <i data-lucide="chef-hat" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>Layar KDS</span>
                    </a>

                    <!-- User Profile & Logout -->
                    <div class="flex items-center gap-2.5 pl-3 border-l border-[#272320]">
                        <div class="w-8 h-8 bg-amber-500/10 border border-amber-500/30 text-amber-400 rounded-xl flex items-center justify-center font-bold text-xs shadow-xs">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:flex flex-col">
                            <span class="text-xs font-bold text-stone-200 leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-[9px] text-amber-400 uppercase tracking-wider font-mono">{{ Auth::user()->role }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="p-1.5 text-stone-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition ml-1" 
                                title="Logout dari Sistem"
                                aria-label="Logout"
                            >
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Scrollable Page Body -->
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 space-y-6">
                    @if ($errors->any())
                        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-300 text-xs space-y-1 shadow-lg">
                            <div class="flex items-center gap-2 font-bold text-rose-400">
                                <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0"></i>
                                <span>Terdapat beberapa kesalahan:</span>
                            </div>
                            <ul class="text-xs text-rose-300/90 space-y-0.5 pl-6 list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-lg">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-2xl text-xs font-semibold flex items-center justify-between shadow-lg">
                            <div class="flex items-center gap-2.5">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-400 shrink-0"></i>
                                <span>{{ session('error') }}</span>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
        function initLucideIcons() {
            if (window.lucide) {
                lucide.createIcons();
            }
        }
        document.addEventListener('DOMContentLoaded', initLucideIcons);
        document.addEventListener('livewire:navigated', initLucideIcons);
        document.addEventListener('livewire:initialized', () => {
            if (window.Livewire) {
                Livewire.hook('morph.updated', initLucideIcons);
                Livewire.hook('commit', initLucideIcons);
            }
        });
    </script>
</body>
</html>
