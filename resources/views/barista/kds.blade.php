<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0E0D0D]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Display System (KDS) — BrewPOS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="{{ asset('js/lucide.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
    <div class="h-screen flex flex-col bg-[#0E0D0D]">
        <!-- Top Navigation Header -->
        <header class="surface-card text-stone-100 px-5 py-3 border-b border-border-default flex justify-between items-center z-20 shrink-0">
            <!-- Brand & Barista PIC Info -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 font-bold shrink-0">
                        <i data-lucide="coffee" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-extrabold tracking-tight leading-none text-stone-100 flex items-center gap-2">
                            BrewPOS <span class="text-[10px] uppercase font-mono tracking-wider px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">Kitchen Display</span>
                        </h1>
                        <p class="text-xs text-stone-400 mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3 h-3 text-amber-500/80"></i>
                            <span>Operator: <strong class="font-medium text-stone-200">{{ auth()->user()->name }}</strong> ({{ ucfirst(auth()->user()->role) }})</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Live Clock & Actions -->
            <div class="flex items-center gap-2.5">
                <!-- Live Digital Clock -->
                <div class="bg-[#0E0D0D] border border-[#2A2724] px-3 py-1.5 rounded-lg flex items-center gap-2 shadow-inner">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span id="digital-clock" class="text-xs font-mono font-bold text-emerald-400 tracking-wider">00:00:00 AM</span>
                </div>

                <!-- Sound Alert Toggle -->
                <button 
                    id="sound-toggle-btn" 
                    onclick="toggleSound()" 
                    class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5"
                    title="Aktifkan/Bisukan nada notifikasi"
                >
                    <i data-lucide="volume-2" id="sound-icon" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span id="sound-label">Audio ON</span>
                </button>

                <!-- Navigation Back to Dashboard / POS if applicable -->
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5">
                        <i data-lucide="layout-dashboard" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>Dashboard</span>
                    </a>
                @endif

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-ghost text-xs py-1.5 px-3 text-stone-400 hover:text-rose-400 hover:border-rose-800/40 flex items-center gap-1.5">
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

        // Live Digital Clock (12-hour format with AM/PM)
        function updateClock() {
            const now = new Date();
            const clockEl = document.getElementById('digital-clock');
            if (clockEl) {
                clockEl.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Lucide Icon Rendering Hooks
        function renderLucideIcons() {
            if (window.lucide && document.querySelector('i[data-lucide]')) {
                window.lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) window.lucide.createIcons();
        });
        document.addEventListener('livewire:navigated', () => {
            if (window.lucide) window.lucide.createIcons();
        });

        document.addEventListener('livewire:initialized', () => {
            if (window.lucide) window.lucide.createIcons();

            Livewire.hook('morph.updated', () => {
                renderLucideIcons();
            });
            Livewire.hook('commit', () => {
                renderLucideIcons();
            });

            // MutationObserver to catch any dynamically injected icons without loop
            let isRendering = false;
            const observer = new MutationObserver((mutations) => {
                if (isRendering) return;
                const hasLucideTags = mutations.some(m => 
                    Array.from(m.addedNodes).some(node => 
                        node.nodeType === 1 && (node.matches('i[data-lucide]') || node.querySelector('i[data-lucide]'))
                    )
                );
                if (hasLucideTags && window.lucide) {
                    isRendering = true;
                    window.lucide.createIcons();
                    setTimeout(() => { isRendering = false; }, 50);
                }
            });
            observer.observe(document.body, { childList: true, subtree: true });
        });
    </script>
</body>
</html>
