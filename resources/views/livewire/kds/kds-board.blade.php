<div wire:poll.5s class="p-5 md:p-6 bg-[#0E0D0D] text-[#F3F0EA] min-h-screen font-sans">
    <!-- KDS Header Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-[#2A2724]">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 font-bold shrink-0">
                    <i data-lucide="chef-hat" class="w-4 h-4"></i>
                </div>
                <h2 class="text-lg font-bold tracking-tight text-white flex items-center gap-2.5">
                    <span>KITCHEN & BARISTA DISPLAY (KDS)</span>
                    <span class="text-[10px] uppercase font-mono tracking-wider px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> Realtime (5s)
                    </span>
                </h2>
            </div>
            <p class="text-xs text-[#A8A29E] mt-1">Layar pemantauan antrian racikan minuman & makanan langsung dari kasir</p>
        </div>

        <!-- Queue Summary Counters -->
        <div class="flex items-center gap-2 flex-wrap">
            <div class="px-3 py-1.5 rounded-lg bg-[#181716] border border-[#2A2724] flex items-center gap-2 text-xs">
                <span class="text-stone-400">Total Aktif:</span>
                <span class="font-mono font-bold text-white">{{ $stats['total'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-lg bg-blue-500/10 border border-blue-500/20 flex items-center gap-1.5 text-xs text-blue-400">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                <span>Baru:</span>
                <span class="font-mono font-bold">{{ $stats['paid'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center gap-1.5 text-xs text-amber-400">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                <span>Diracik:</span>
                <span class="font-mono font-bold">{{ $stats['processing'] ?? 0 }}</span>
            </div>
            <div class="px-2.5 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center gap-1.5 text-xs text-emerald-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Siap:</span>
                <span class="font-mono font-bold">{{ $stats['ready'] ?? 0 }}</span>
            </div>

            <button 
                wire:click="$refresh" 
                class="btn btn-secondary text-xs py-1.5 px-3 flex items-center gap-1.5 ml-1"
                title="Muat Ulang Antrian"
            >
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-amber-500"></i>
                <span class="hidden sm:inline">Refresh</span>
            </button>
        </div>
    </div>

    <!-- Main Content Area -->
    @if($ordersList->isEmpty())
        <div class="flex flex-col items-center justify-center py-24 bg-[#181716] rounded-xl border border-dashed border-[#2A2724] text-center max-w-lg mx-auto">
            <div class="w-14 h-14 rounded-xl bg-[#141312] border border-[#2A2724] flex items-center justify-center text-stone-500 mb-3.5">
                <i data-lucide="coffee" class="w-7 h-7 opacity-50"></i>
            </div>
            <h3 class="text-sm font-bold text-stone-200">Belum Ada Pesanan Aktif</h3>
            <p class="text-xs text-stone-500 mt-1 max-w-xs">Pesanan yang dibuat oleh kasir akan muncul secara otomatis di papan antrian ini.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($ordersList as $order)
                @php
                    $isCompleted = $order->status === 'completed';
                    $isReady = $order->status === 'ready';
                    $isProcessing = $order->status === 'processing';
                    $minutesAgo = (int) $order->created_at->diffInMinutes(now());
                    
                    $cardClass = $isCompleted 
                        ? 'border-[#2A2724] bg-[#121110] opacity-40 grayscale' 
                        : ($isReady 
                            ? 'border-emerald-500/40 bg-[#181716]' 
                            : ($isProcessing 
                                ? 'border-amber-500/50 bg-[#181716] shadow-lg shadow-black/20' 
                                : 'border-blue-500/40 bg-[#181716] shadow-lg shadow-black/20'));
                @endphp

                <div 
                    class="order-card rounded-lg border {{ $cardClass }} p-4 flex flex-col justify-between transition-all"
                    data-order-id="{{ $order->id }}"
                >
                    <div>
                        <!-- Ticket Header -->
                        <div class="flex justify-between items-start border-b border-[#2A2724] pb-3 mb-3">
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    {{-- Order Status Badge --}}
                                    @if($isCompleted)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-stone-500/20 text-stone-300 border border-stone-500/30">
                                            <i data-lucide="check" class="w-3 h-3 text-stone-300"></i>
                                            <span>SELESAI</span>
                                        </span>
                                    @elseif($isReady)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                            <i data-lucide="bell" class="w-3 h-3 text-emerald-400"></i>
                                            <span>SIAP</span>
                                        </span>
                                    @elseif($isProcessing)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                            <i data-lucide="flame" class="w-3 h-3 text-amber-400"></i>
                                            <span>RACIK</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase tracking-wider bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400 animate-pulse"></span>
                                            <span>BARU</span>
                                        </span>
                                    @endif

                                    {{-- Order Aging Timer Badge --}}
                                    @if($minutesAgo > 10)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                            <i data-lucide="alert-triangle" class="w-3 h-3 text-rose-400"></i>
                                            <span>Urgent ({{ $minutesAgo }}m)</span>
                                        </span>
                                    @elseif($minutesAgo > 5)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            <i data-lucide="clock" class="w-3 h-3 text-amber-400"></i>
                                            <span>{{ $minutesAgo }}m</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            <i data-lucide="clock" class="w-3 h-3 text-emerald-400"></i>
                                            <span>{{ $minutesAgo < 1 ? '< 1m' : $minutesAgo . 'm' }}</span>
                                        </span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-mono font-bold mt-1.5 text-white tracking-tight">#{{ $order->order_number }}</h3>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-stone-400 font-mono font-semibold block">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                        </div>

                        <!-- Order Items List -->
                        <div class="space-y-2 my-2">
                            @foreach($order->orderItems as $item)
                                <div class="bg-[#141312] p-2.5 rounded-lg border border-[#2A2724]">
                                    <div class="flex justify-between items-center">
                                        <span class="font-bold text-xs text-white">{{ $item->product->name ?? 'Produk' }}</span>
                                        <span class="text-xs font-mono font-bold text-amber-400 bg-amber-500/10 px-1.5 py-0.5 rounded border border-amber-500/20">
                                            x{{ $item->quantity }}
                                        </span>
                                    </div>
                                    <div class="flex gap-1.5 text-[10px] text-stone-400 mt-1 font-mono">
                                        <span class="bg-[#181716] px-1.5 py-0.5 rounded border border-[#2A2724]">Ukuran: {{ $item->size }}</span>
                                        @if($item->sugar_level)
                                            <span class="bg-[#181716] px-1.5 py-0.5 rounded border border-[#2A2724]">Gula: {{ $item->sugar_level }}</span>
                                        @endif
                                    </div>
                                    @if($item->notes)
                                        <p class="text-[11px] text-amber-300 italic mt-1.5 bg-amber-500/10 p-1.5 rounded border border-amber-500/20 flex items-center gap-1.5">
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
                                class="btn btn-primary w-full py-2.5 text-xs uppercase tracking-wider font-bold flex items-center justify-center gap-2 cursor-pointer shadow-sm active:scale-[0.99]"
                            >
                                <i data-lucide="coffee" class="w-4 h-4"></i>
                                <span>Mulai Racik Pesanan</span>
                            </button>
                        @elseif($order->status === 'processing')
                            <button 
                                wire:click="updateStatus({{ $order->id }}, 'ready')" 
                                class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition-colors flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]"
                            >
                                <i data-lucide="bell" class="w-4 h-4"></i>
                                <span>Tandai Siap Disajikan</span>
                            </button>
                        @elseif($order->status === 'ready')
                            <button 
                                wire:click="updateStatus({{ $order->id }}, 'completed')" 
                                class="btn btn-secondary w-full py-2.5 text-xs uppercase tracking-wider font-bold flex items-center justify-center gap-2 cursor-pointer active:scale-[0.99]"
                            >
                                <i data-lucide="check-check" class="w-4 h-4"></i>
                                <span>Selesai / Arsipkan</span>
                            </button>
                        @else
                            <!-- Completed state with 2-minute dim state (SRS KDS-05) -->
                            <div class="w-full py-2 bg-stone-900/60 text-stone-400 font-semibold text-xs text-center rounded-lg border border-stone-800 flex items-center justify-center gap-1.5 select-none">
                                <i data-lucide="check-check" class="w-4 h-4 text-stone-500"></i>
                                <span>Pesanan Selesai (Fading 2m)</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
