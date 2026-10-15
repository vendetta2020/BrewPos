<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) — BrewPOS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/lucide@0.344.0/dist/umd/lucide.min.js"></script>
    <script>
        // Graceful Echo fallback to avoid console warnings in non-websocket environments
        if (typeof window.Echo === 'undefined') {
            window.Echo = {
                socketId: () => undefined,
                channel: () => ({
                    listen: () => ({}),
                    stopListening: () => ({})
                }),
                private: () => ({
                    listen: () => ({}),
                    stopListening: () => ({})
                })
            };
        }

        // Suppress benign AbortError console messages caused by rapid Livewire polling cancellations
        window.addEventListener('unhandledrejection', (event) => {
            if (event.reason && (event.reason.name === 'AbortError' || event.reason.status === null)) {
                event.preventDefault();
            }
        });
    </script>
</head>
<body class="bg-[#0E0D0D] text-[#F3F0EA] font-sans h-full overflow-hidden antialiased selection:bg-amber-500 selection:text-black">
    <!-- KDS Root Container -->
    <div class="h-screen flex flex-col">
        <!-- Top Navigation Header -->
        <header class="bg-[#181716] text-[#F3F0EA] px-5 py-3 border-b border-[#2D2A26] flex justify-between items-center z-20 shrink-0">
            <!-- Brand & Barista PIC Info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-600/20 border border-amber-500/30 flex items-center justify-center text-amber-500 font-bold">
                        <i data-lucide="coffee" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold tracking-tight leading-none text-white flex items-center gap-2">
                            BrewPOS <span class="text-[10px] uppercase font-mono tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Kitchen Display</span>
                        </h1>
                        <p class="text-xs text-[#A8A29E] mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3 h-3 text-amber-500/80"></i>
                            <span>Operator: <strong class="font-medium text-[#E7E5E4]">{{ auth()->user()->name }}</strong> ({{ ucfirst(auth()->user()->role) }})</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Live Clock & Actions -->
            <div class="flex items-center gap-3">
                <!-- Live Digital Clock -->
                <div class="bg-[#242220] border border-[#383430] px-3.5 py-1.5 rounded-lg flex items-center gap-2">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span id="digital-clock" class="text-xs font-mono font-bold text-emerald-400 tracking-wider">00:00:00</span>
                </div>

                <!-- Sound Alert Toggle -->
                <button 
                    id="sound-toggle-btn" 
                    onclick="toggleSound()" 
                    class="px-3 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition-colors bg-[#242220] border-[#383430] text-stone-300 hover:text-white"
                    title="Aktifkan/Bisukan nada notifikasi"
                >
                    <i data-lucide="volume-2" id="sound-icon" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span id="sound-label">Audio ON</span>
                </button>

                <!-- Navigation Back to Dashboard / POS if applicable -->
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="text-xs font-semibold px-3 py-2 bg-[#242220] hover:bg-[#2D2A26] text-stone-200 border border-[#383430] rounded-lg transition-colors flex items-center gap-1.5">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Dashboard</span>
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
        <main class="flex-1 overflow-auto bg-[#0E0D0D]">
            @livewire('kds.kds-board')
        </main>
    </div>

    @livewireScripts
    <script>
        // Audio Chime Synthesizer using Web Audio API (SRS KDS-04 audio cue)
        let soundEnabled = true;

        function toggleSound() {
            soundEnabled = !soundEnabled;
            const label = document.getElementById('sound-label');
            const icon = document.getElementById('sound-icon');
            if (soundEnabled) {
                label.textContent = 'Audio ON';
                playChime();
            } else {
                label.textContent = 'Audio OFF';
            }
        }

        function playChime() {
            if (!soundEnabled) return;
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(587.33, ctx.currentTime); // D5
                osc.frequency.setValueAtTime(880, ctx.currentTime + 0.1); // A5
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } catch (e) {
                // AudioContext not allowed or unsupported
            }
        }

        // Live Digital Clock
        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('digital-clock');
            if (clockEl) {
                clockEl.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Lucide Icon Rendering Hooks
        function renderLucideIcons() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', renderLucideIcons);
        document.addEventListener('livewire:navigated', renderLucideIcons);

        document.addEventListener('livewire:initialized', () => {
            Livewire.hook('morph.updated', () => {
                renderLucideIcons();
            });
            Livewire.hook('commit', () => {
                renderLucideIcons();
            });
        });
    </script>
</body>
</html>
