<div wire:poll.5s class="p-6 bg-[#0E0D0D] text-[#F3F0EA] min-h-screen font-sans">
    <!-- KDS Header Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-[#2D2A26]">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 font-bold">
                    <i data-lucide="chef-hat" class="w-4 h-4"></i>
                </div>
                <h2 class="text-xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    KITCHEN & BARISTA DISPLAY (KDS)
                    <span class="text-[10px] uppercase font-mono tracking-wider px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Realtime (5s)
                    </span>
                </h2>
            </div>
            <p class="text-xs text-[#A8A29E] mt-1">Layar pemantauan antrian racikan minuman & makanan langsung dari kasir</p>
        </div>

        <!-- Queue Summary Counters -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="px-3 py-1.5 rounded-xl bg-[#181716] border border-[#2D2A26] flex items-center gap-2 text-xs">
                <span class="text-stone-400">Total Aktif:</span>
                <span class="font-mono font-bold text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center gap-1.5 text-xs text-blue-400">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                <span>Baru:</span>
                <span class="font-mono font-bold">{{ $stats['paid'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center gap-1.5 text-xs text-amber-400">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>Diracik:</span>
                <span class="font-mono font-bold">{{ $stats['processing'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-1.5 text-xs text-emerald-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Siap:</span>
                <span class="font-mono font-bold">{{ $stats['ready'] ?? 0 }}</span>
            </div>

            <button 
                wire:click="$refresh" 
                class="px-3.5 py-2 bg-[#181716] hover:bg-[#242220] text-xs font-semibold text-stone-300 hover:text-white rounded-xl border border-[#2D2A26] transition-all flex items-center gap-2 ml-1"
                title="Muat Ulang Antrian"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-400"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    @if($ordersList->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 bg-[#141312] rounded-2xl border border-dashed border-[#2D2A26] text-center max-w-xl mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-[#181716] border border-[#2D2A26] flex items-center justify-center text-[#57534E] mb-4">
                <i data-lucide="coffee" class="w-8 h-8 opacity-60"></i>
            </div>
            <h3 class="text-base font-bold text-[#F3F0EA]">Belum Ada Pesanan Aktif</h3>
            <p class="text-xs text-[#78716C] mt-1 max-w-sm">Pesanan yang dibuat oleh kasir akan muncul secara otomatis di papan antrian ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($ordersList as $order)
                @php
                    $isCompleted = $order->status === 'completed';
                    $isReady = $order->status === 'ready';
                    $isProcessing = $order->status === 'processing';
                    $minutesAgo = (int) $order->created_at->diffInMinutes(now());
                    
                    // Order aging color tag logic
                    if ($minutesAgo > 10) {
                        $timerBadgeClass = 'bg-rose-500/20 text-rose-400 border-rose-500/30';
                        $timerText = '🔥 Urgent (' . $minutesAgo . 'm)';
                    } elseif ($minutesAgo > 5) {
                        $timerBadgeClass = 'bg-amber-500/20 text-amber-400 border-amber-500/30';
                        $timerText = '⏳ Wait (' . $minutesAgo . 'm)';
                    } else {
                        $timerBadgeClass = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30';
                        $timerText = '⚡ Fresh (' . ($minutesAgo < 1 ? '< 1m' : $minutesAgo . 'm') . ')';
                    }

                    $cardClass = $isCompleted 
                        ? 'border-[#2D2A26] bg-[#121110] opacity-50 grayscale' 
                        : ($isReady 
                            ? 'border-emerald-500/40 bg-[#161514] opacity-90' 
                            : ($isProcessing 
                                ? 'border-amber-500/70 shadow-lg shadow-amber-500/5' 
                                : 'border-blue-500/70 shadow-lg shadow-blue-500/5'));
                @endphp

                <div 
                    class="order-card bg-[#181716] rounded-2xl border {{ $cardClass }} p-4 flex flex-col justify-between transition-all"
                    data-order-id="{{ $order->id }}"
                >
                    <div>
                        <!-- Ticket Header -->
                        <div class="flex justify-between items-start border-b border-[#2A2724] pb-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono font-bold uppercase px-2 py-0.5 rounded border {{ $isCompleted ? 'bg-stone-500/20 text-stone-300 border-stone-500/30' : ($isReady ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($isProcessing ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20')) }}">
                                        {{ $isCompleted ? '✓ SELESAI' : ($isReady ? '✓ SIAP' : ($isProcessing ? '⚡ RACIK' : '🆕 BARU')) }}
                                    </span>
                                    <span class="text-[10px] font-mono font-semibold px-2 py-0.5 rounded border {{ $timerBadgeClass }}">
                                        {{ $timerText }}
                                    </span>
                                </div>
                                <h3 class="text-xl font-mono font-extrabold mt-1.5 text-white tracking-wide">#{{ $order->order_number }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-[#A8A29E] font-mono font-semibold block">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                        </div>

                        <!-- Order Items List -->
                        <div class="space-y-2.5 my-2">
                            @foreach($order->orderItems as $item)
                                <div class="bg-[#141312] p-2.5 rounded-xl border border-[#2A2724]">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-xs text-[#F3F0EA]">{{ $item->product->name ?? 'Produk' }}</span>
                                        <span class="text-xs font-mono font-extrabold text-amber-400 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20">
                                            x{{ $item->quantity }}
                                        </span>
                                    </div>
                                    <div class="flex gap-1.5 text-[10px] text-[#A8A29E] mt-1">
                                        <span class="bg-[#242220] px-1.5 py-0.2 rounded border border-[#383430]">Ukuran: {{ $item->size }}</span>
                                        @if($item->sugar_level)
                                            <span class="bg-[#242220] px-1.5 py-0.2 rounded border border-[#383430]">Gula: {{ $item->sugar_level }}</span>
                                        @endif
                                    </div>
                                    @if($item->notes)
                                        <p class="text-[11px] text-amber-300 italic mt-1 bg-amber-500/10 p-1.5 rounded border border-amber-500/20 flex items-center gap-1">
                                            <i data-lucide="file-text" class="w-3 h-3 text-amber-400 shrink-0"></i>
                                            <span>{{ $item->notes }}</span>
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 pt-3 border-t border-[#2A2724]">
                        @if($order->status === 'paid')
                            <button 
                                wire:click="updateStatus({{ $order->id }}, 'processing')" 
                                class="w-full py-2.5 bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]"
                            >
                                <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                <span>Mulai Racik Pesanan</span>
                            </button>
                        @elseif($order->status === 'processing')
                            <button 
                                wire:click="updateStatus({{ $order->id }}, 'ready')" 
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]"
                            >
                                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                                <span>Tandai Siap Disajikan</span>
                            </button>
                        @elseif($order->status === 'ready')
                            <button 
                                wire:click="updateStatus({{ $order->id }}, 'completed')" 
                                class="w-full py-2.5 bg-[#242220] hover:bg-[#2D2A26] text-stone-300 hover:text-white font-bold text-xs uppercase tracking-wider rounded-xl border border-[#383430] transition-colors flex items-center justify-center gap-2 cursor-pointer active:scale-[0.98]"
                            >
                                <i data-lucide="archive" class="w-3.5 h-3.5"></i>
                                <span>Selesai / Arsipkan</span>
                            </button>
                        @else
                            <!-- Completed state with 2-minute dim state (SRS KDS-05) -->
                            <div class="w-full py-2 bg-[#1a1918] text-stone-400 font-semibold text-[11px] text-center rounded-xl border border-[#2d2a27] flex items-center justify-center gap-1.5 select-none">
                                <i data-lucide="check-check" class="w-3.5 h-3.5 text-stone-400"></i>
                                <span>Pesanan Selesai (Fading 2m)</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
