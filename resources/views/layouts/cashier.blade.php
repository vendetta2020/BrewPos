<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS Cashier') — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans h-full overflow-hidden antialiased selection:bg-amber-500 selection:text-black">
    <!-- POS Root Container -->
    <div class="h-screen flex flex-col">
        <!-- Top Bar Header -->
        <header class="bg-[#181716] text-[#F3F0EA] px-5 py-3 border-b border-[#2D2A26] flex justify-between items-center z-20 shrink-0">
            <!-- Brand Logo & Cashier Info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-600/20 border border-amber-500/30 flex items-center justify-center text-amber-500 font-bold">
                        <i data-lucide="coffee" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold tracking-tight leading-none text-white flex items-center gap-2">
                            BrewPOS <span class="text-[10px] uppercase font-mono tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">POS System</span>
                        </h1>
                        <p class="text-xs text-[#A8A29E] mt-0.5 flex items-center gap-1">
                            <i data-lucide="user" class="w-3 h-3 text-amber-500/80"></i> Kasir: <span class="font-medium text-[#E7E5E4]">{{ auth()->user()->name }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Session Status & Controls -->
            <div class="flex items-center gap-3">
                <!-- Session Status Badge -->
                <div class="bg-[#242220] border border-[#383430] px-3.5 py-1.5 rounded-lg flex items-center gap-3">
                    @if (isset($currentSession))
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2 w-2">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <div>
                                <div class="text-xs font-mono font-bold text-emerald-400 flex items-center gap-1.5">
                                    Sesi #{{ $currentSession->id }} <span class="text-[10px] font-sans font-semibold uppercase px-1 py-0.2 rounded bg-emerald-500/20 text-emerald-300">Terbuka</span>
                                </div>
                                <div class="text-[11px] text-[#A8A29E] font-mono">
                                    Modal: Rp {{ number_format($currentSession->opening_balance, 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-amber-400 text-xs font-semibold">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i>
                            <span>Sesi Belum Dibuka</span>
                        </div>
                    @endif
                </div>

                <!-- Order History Action (SRS POS-10) -->
                @if (isset($currentSession))
                    <a href="{{ route('cashier.orders.index') }}" class="text-xs font-semibold px-3 py-2 bg-[#242220] hover:bg-[#2D2A26] text-stone-200 border border-[#383430] rounded-lg transition-colors flex items-center gap-1.5">
                        <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Riwayat Pesanan</span>
                    </a>

                    <!-- Session Close Action -->
                    <a href="{{ route('cashier.session.close') }}" class="text-xs font-semibold px-3 py-2 bg-amber-600/10 hover:bg-amber-600/20 text-amber-400 border border-amber-600/30 rounded-lg transition-colors flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        <span>Tutup Sesi</span>
                    </a>
                @endif

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-2 bg-[#2D2A26] hover:bg-rose-950/40 text-stone-300 hover:text-rose-400 border border-[#383430] hover:border-rose-800/40 rounded-lg transition-all flex items-center gap-1.5">
                        <i data-lucide="log-out" class="w-3.5 h-3.5"></i>
                        <span>Logout</span>
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
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
        document.addEventListener('livewire:load', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>

