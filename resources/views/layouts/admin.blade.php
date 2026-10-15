<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-[#F8F6F0] text-stone-800 antialiased font-sans h-full selection:bg-amber-500 selection:text-black">
    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
        <!-- Sidebar Backdrop on Mobile -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false" 
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-xs md:hidden"
        ></div>

        <!-- Sidebar Container (Fixed slide-over on mobile, static on md+) -->
        <div 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" 
            class="fixed inset-y-0 left-0 z-50 md:static transition-transform duration-200 ease-in-out shrink-0 flex h-full"
        >
            @include('components.sidebar')
        </div>

        <!-- Main Workspace -->
        <div class="flex-1 flex flex-col overflow-hidden min-w-0">
            <!-- Top Bar Header -->
            <header class="h-16 bg-white border-b border-stone-200/80 px-4 sm:px-6 flex items-center justify-between shrink-0 z-10 shadow-2xs">
                <div class="flex items-center gap-3">
                    <button 
                        @click="sidebarOpen = !sidebarOpen" 
                        class="md:hidden p-2 text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition"
                        aria-label="Buka Navigasi Menu"
                    >
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight leading-tight">
                            @yield('page_title', 'Dashboard')
                        </h1>
                        <p class="text-[11px] text-stone-500 hidden sm:block">Panel Pengelolaan Operasional & Inventori</p>
                    </div>
                </div>

                <!-- Right Top Bar Controls -->
                <div class="flex items-center gap-3">
                    <!-- Quick Link to POS -->
                    <a 
                        href="{{ route('pos') }}" 
                        target="_blank"
                        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-800 text-xs font-semibold rounded-lg border border-amber-500/20 transition-colors"
                        title="Buka Kasir POS"
                    >
                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Buka POS</span>
                    </a>

                    <!-- User Avatar & Dropdown -->
                    <div class="flex items-center gap-2.5 pl-3 border-l border-stone-200">
                        <div class="w-8 h-8 bg-stone-900 border border-stone-700 text-amber-400 rounded-lg flex items-center justify-center font-bold text-xs shadow-xs">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="hidden sm:flex flex-col">
                            <span class="text-xs font-bold text-stone-900 leading-tight">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] text-stone-500 uppercase tracking-wider font-mono">{{ Auth::user()->role }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button 
                                type="submit" 
                                class="p-1.5 text-stone-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" 
                                title="Logout dari Sistem"
                                aria-label="Logout"
                            >
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content Scrollable Container -->
            <main class="flex-1 overflow-y-auto">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6">
                    @if ($errors->any())
                        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-xl">
                            <div class="flex items-center gap-2 text-rose-800 text-xs font-bold mb-1.5">
                                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                                <span>Terdapat beberapa kesalahan:</span>
                            </div>
                            <ul class="text-xs text-rose-700 space-y-1 pl-6 list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-5 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center gap-2.5">
                            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-semibold flex items-center gap-2.5">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                            <span>{{ session('error') }}</span>
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
