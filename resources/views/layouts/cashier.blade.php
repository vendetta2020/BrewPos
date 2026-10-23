<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS Cashier') — BrewPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @livewireStyles
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans h-full overflow-hidden antialiased selection:bg-amber-500 selection:text-black">
    <!-- POS Root Container -->
    <div class="h-screen flex flex-col">
        <!-- Top Bar Header -->
        <header class="bg-[#181716] text-[#F3F0EA] px-4 sm:px-5 py-2.5 border-b border-[#2A2724] flex justify-between items-center z-20 shrink-0">
            <!-- Brand Logo & Cashier Info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 font-bold">
                        <i data-lucide="coffee" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-bold tracking-tight leading-none text-white flex items-center gap-2">
                            BrewPOS <span class="text-[9px] uppercase font-mono tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">POS</span>
                        </h1>
                        <p class="text-[11px] text-stone-400 mt-0.5 flex items-center gap-1">
                            <i data-lucide="user" class="w-3 h-3 text-stone-500"></i> Kasir: <span class="font-medium text-stone-300">{{ auth()->user()->name }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Session Status & Controls -->
            <div class="flex items-center gap-2.5">
                <!-- Session Status Badge -->
                <div class="bg-stone-900/60 border border-stone-800 px-3 py-1.5 rounded-lg flex items-center gap-2.5">
                    @if (isset($currentSession))
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <div>
                                <div class="text-xs font-mono font-medium text-stone-200 flex items-center gap-1.5">
                                    Sesi #{{ $currentSession->id }} <span class="badge badge-emerald py-0 px-1 text-[9px]">Terbuka</span>
                                </div>
                                <div class="text-[11px] text-stone-400 font-mono">
                                    Modal: Rp {{ number_format($currentSession->opening_balance, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-amber-400 text-xs font-medium">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                            <span>Sesi Belum Dibuka</span>
                        </div>
                    @endif
                </div>

                <!-- Order History Action (SRS POS-10) -->
                @if (isset($currentSession))
                    <a href="{{ route('cashier.orders.index') }}" class="btn btn-secondary text-xs px-3 py-1.5 inline-flex items-center gap-1.5">
                        <i data-lucide="receipt" class="w-3.5 h-3.5 text-stone-400"></i>
                        <span>Riwayat</span>
                    </a>

                    <!-- Session Close Action -->
                    <a href="{{ route('cashier.session.close') }}" class="btn btn-outline text-xs px-3 py-1.5 inline-flex items-center gap-1.5 text-amber-400 border-amber-600/30 hover:bg-amber-600/10">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        <span>Tutup Sesi</span>
                    </a>
                @endif

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-xs px-2.5 py-1.5 inline-flex items-center gap-1.5 text-stone-400 hover:text-rose-400">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </button>
                </form>
            </div>
        </header>

        <!-- Main Workspace Area -->
        <main class="flex-1 flex overflow-hidden bg-[#0E0D0D]">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
    @livewireScripts
    <script>
        function initLucideIcons() {
            if (window.lucide && document.querySelector('i[data-lucide]')) {
                window.lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', initLucideIcons);
        document.addEventListener('livewire:navigated', initLucideIcons);

        document.addEventListener('livewire:initialized', () => {
            initLucideIcons();
            if (window.Livewire) {
                Livewire.hook('morph.updated', () => {
                    queueMicrotask(initLucideIcons);
                });
                Livewire.hook('commit', ({ succeed }) => {
                    succeed(() => {
                        queueMicrotask(initLucideIcons);
                    });
                });
            }
        });
    </script>
</body>
</html>

