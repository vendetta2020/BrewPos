<div class="h-full flex-1 flex flex-col overflow-hidden">
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Column: Product Catalog Workspace -->
        <div class="flex-1 flex flex-col bg-[#0E0D0D] border-r border-[#2D2A26] overflow-hidden">
            <!-- Filter & Search Toolbar -->
            <div class="p-3 bg-[#141312] border-b border-stone-800 flex flex-col sm:flex-row gap-3 items-center justify-between z-10 shrink-0">
                <!-- Search Input -->
                <div class="relative w-full sm:w-72">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-stone-500">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                    <input 
                        wire:model.debounce.300ms="search" 
                        wire:keydown.enter.prevent="scanBarcode"
                        type="text" 
                        id="pos-search-input"
                        placeholder="Cari menu atau scan barcode..." 
                        class="form-input pl-9 pr-14 text-xs"
                    >
                    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center gap-1">
                        @if($search)
                            <button wire:click="$set('search', '')" class="text-stone-500 hover:text-stone-300" aria-label="Bersihkan pencarian">
                                <i data-lucide="x" class="w-3.5 h-3.5"></i>
                            </button>
                        @endif
                        <span class="hidden sm:inline-block text-[10px] text-stone-500 border border-stone-700 bg-stone-900 px-1.5 py-0.5 rounded font-mono select-none" title="Tekan / untuk fokus pencarian">/</span>
                    </div>
                </div>

                <!-- Category Pills Horizontal Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 no-scrollbar">
                    <button 
                        wire:key="category-all"
                        wire:click="$set('selectedCategory', null)"
                        class="px-3 py-1.5 text-xs rounded-lg transition-all shrink-0 flex items-center gap-1.5 {{ is_null($selectedCategory) ? 'bg-amber-600 text-stone-950 font-bold' : 'bg-stone-900/40 text-stone-400 hover:text-stone-200 border border-stone-800' }}"
                    >
                        <span>Semua Menu</span>
                    </button>
                    @foreach($categories as $cat)
                        <button 
                            wire:key="category-{{ $cat->id }}"
                            wire:click="$set('selectedCategory', {{ $cat->id }})"
                            class="px-3 py-1.5 text-xs rounded-lg transition-all shrink-0 flex items-center gap-1.5 {{ $selectedCategory == $cat->id ? 'bg-amber-600 text-stone-950 font-bold' : 'bg-stone-900/40 text-stone-400 hover:text-stone-200 border border-stone-800' }}"
                        >
                            <span>{{ $cat->name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            @if(session('success'))
                <div class="mx-4 mt-3 p-3 bg-emerald-500/10 border border-emerald-500/30 rounded-xl flex items-center justify-between text-xs text-emerald-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button wire:click="$refresh" class="text-emerald-400/80 hover:text-emerald-200">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            @if(session('warning') || session('error'))
                <div class="mx-4 mt-3 p-3 bg-amber-500/10 border border-amber-500/30 rounded-xl flex items-center justify-between text-xs text-amber-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-400 shrink-0"></i>
                        <span>{{ session('warning') ?? session('error') }}</span>
                    </div>
                    <button wire:click="$refresh" class="text-amber-400/80 hover:text-amber-200">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            @endif

            <!-- Product Grid Workspace -->
            <div class="flex-1 overflow-y-auto p-4" id="menu-grid">
                @if($products->isEmpty())
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 border border-dashed border-stone-800 rounded-2xl bg-stone-900/30">
                        <div class="w-12 h-12 rounded-full bg-stone-900 border border-stone-800 flex items-center justify-center text-stone-500 mb-3">
                            <i data-lucide="package-x" class="w-6 h-6"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-stone-200">Menu tidak ditemukan</h4>
                        <p class="text-xs text-stone-500 mt-1">Coba sesuaikan kata kunci pencarian atau kategori.</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
                        @foreach($products as $product)
                            @php
                                $stockStatus = $product->getStockStatus('M');
                                $isAvailable = $stockStatus !== 'Habis';
                            @endphp
                            <div 
                                wire:key="pos-product-{{ $product->id }}"
                                @if($isAvailable)
                                    wire:click="addToCart({{ $product->id }}, 'M')"
                                    role="button"
                                    tabindex="0"
                                    wire:loading.class="opacity-75"
                                @else
                                    aria-disabled="true"
                                @endif
                                class="group surface-card {{ $isAvailable ? 'hover:border-amber-500/50 cursor-pointer active:scale-[0.99]' : 'opacity-50 cursor-not-allowed border-rose-900/30' }} p-3 flex flex-col justify-between transition-all select-none"
                            >
                                <div>
                                    <!-- Product Image Container -->
                                    <div class="h-24 rounded-lg surface-card-inset flex items-center justify-center text-stone-400 {{ $isAvailable ? 'group-hover:scale-[1.01]' : 'grayscale' }} transition-transform overflow-hidden relative">
                                        @if(!empty($product->image))
                                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="coffee" class="w-7 h-7 text-stone-500 group-hover:text-amber-500 transition-colors"></i>
                                        @endif

                                        <span class="absolute top-1.5 right-1.5 bg-black/75 backdrop-blur-sm text-[10px] font-mono text-stone-200 font-semibold px-1.5 py-0.5 rounded border border-stone-800">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </span>

                                        @if(!$isAvailable)
                                            <div class="absolute inset-0 bg-black/70 flex items-center justify-center">
                                                <span class="badge badge-rose">
                                                    Habis
                                                </span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Details -->
                                    <div class="mt-2.5">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[10px] font-medium text-stone-500 uppercase tracking-wider block">
                                                {{ $product->category->name ?? 'Minuman' }}
                                            </span>
                                            @if(!empty($product->barcode))
                                                <span class="text-[9px] font-mono text-stone-400 bg-stone-900 px-1 rounded border border-stone-800">
                                                    {{ $product->barcode }}
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="font-medium text-xs text-stone-200 mt-0.5 line-clamp-1 {{ $isAvailable ? 'group-hover:text-amber-400' : 'text-stone-500' }} transition-colors">
                                            {{ $product->name }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-t border-stone-800/80 flex items-center justify-between text-[11px]">
                                    @if($stockStatus === 'Habis')
                                        <span class="badge badge-rose text-[9px]">Habis</span>
                                        <span class="text-stone-600 p-1 cursor-not-allowed">
                                            <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                        </span>
                                    @elseif($stockStatus === 'Stok Menipis')
                                        <span class="badge badge-amber text-[9px]">Menipis</span>
                                        <button 
                                            type="button"
                                            wire:click.stop="addToCart({{ $product->id }}, 'M')"
                                            class="btn btn-secondary p-1 rounded text-stone-300 hover:text-white"
                                            title="Tambah ke pesanan"
                                        >
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @else
                                        <span class="badge badge-emerald text-[9px]">Tersedia</span>
                                        <button 
                                            type="button"
                                            wire:click.stop="addToCart({{ $product->id }}, 'M')"
                                            class="btn btn-secondary p-1 rounded text-stone-300 hover:text-white"
                                            title="Tambah ke pesanan"
                                        >
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Cart Drawer Sidebar -->
        <div class="w-80 lg:w-96 flex flex-col bg-[#141312] border-l border-stone-800 shadow-2xl z-10 shrink-0">
            <!-- Cart Header -->
            <div class="surface-card px-4 py-3 border-b border-stone-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500">
                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    </div>
                    <h2 class="text-xs font-semibold text-stone-200 uppercase tracking-wider">Keranjang Pesanan</h2>
                </div>
                
                @if(!empty($cart))
                    <div class="flex items-center gap-2">
                        <span class="badge badge-stone">
                            {{ count($cart) }} Menu
                        </span>
                        <button 
                            wire:click.prevent="clearCart" 
                            class="btn btn-ghost px-2 py-1 text-xs text-stone-400 hover:text-rose-400 inline-flex items-center gap-1"
                            title="Kosongkan Keranjang"
                        >
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                @endif
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5">
                @if(empty($cart))
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-12 h-12 rounded-xl surface-card flex items-center justify-center text-stone-600 mb-3">
                            <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-xs font-semibold text-stone-400 uppercase tracking-wider">Keranjang Kosong</h3>
                        <p class="text-[11px] text-stone-500 mt-1 max-w-[200px]">Pilih menu di sebelah kiri untuk menambah pesanan kasir.</p>
                    </div>
                @else
                    @foreach($cart as $key => $item)
                        <div wire:key="cart-item-{{ $key }}" class="surface-card-inset p-3 rounded-xl space-y-2 hover:border-stone-700 transition-colors">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium text-xs text-stone-200 line-clamp-1">{{ $item['name'] }}</h4>
                                    <span class="text-[10px] text-stone-400 font-mono bg-stone-900 px-1.5 py-0.5 rounded border border-stone-800 inline-block mt-0.5">
                                        Ukuran {{ $item['size'] }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono font-medium text-xs text-stone-200">
                                        Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Stepper Controls & Delete -->
                            <div class="pt-1.5 flex items-center justify-between border-t border-stone-800">
                                <button 
                                    wire:click.prevent="removeFromCart('{{ $key }}')" 
                                    class="text-[11px] text-stone-500 hover:text-rose-400 transition-colors flex items-center gap-1"
                                >
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    <span>Hapus</span>
                                </button>

                                <div class="flex items-center gap-1.5 bg-stone-900 border border-stone-800 rounded-lg p-0.5">
                                    <button 
                                        wire:click.prevent="updateQty('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                        class="w-6 h-6 rounded bg-stone-800 hover:bg-amber-600 text-stone-300 hover:text-white font-bold flex items-center justify-center text-xs transition-colors"
                                    >-</button>
                                    
                                    <span class="w-7 text-center font-mono font-medium text-xs text-stone-200">
                                        {{ $item['quantity'] }}
                                    </span>

                                    <button 
                                        wire:click.prevent="updateQty('{{ $key }}', {{ $item['quantity'] + 1 }})" 
                                        class="w-6 h-6 rounded bg-stone-800 hover:bg-amber-600 text-stone-300 hover:text-white font-bold flex items-center justify-center text-xs transition-colors"
                                    >+</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Cart Footer Action -->
            <div class="surface-card border-t border-stone-800 p-4 space-y-3 shrink-0">
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-stone-400">
                        <span>Subtotal ({{ count($cart) }} item)</span>
                        <span class="font-mono text-stone-200">Rp {{ number_format($this->cartTotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-baseline pt-1.5 border-t border-stone-800">
                        <span class="font-semibold text-xs text-stone-300 uppercase tracking-wider">Total Tagihan</span>
                        <span class="font-mono font-bold text-lg text-stone-100 tabular-nums">
                            Rp {{ number_format($this->cartTotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="pt-1">
                    <button 
                        wire:click="emitOpenCheckout" 
                        @if(empty($cart)) disabled @endif
                        class="btn btn-primary w-full py-2.5 text-xs uppercase tracking-wider font-bold disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                    >
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Bayar (Checkout)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewire('pos.checkout-modal', key('pos-checkout-modal'))

    <div id="pos-toast" class="fixed bottom-5 right-5 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <div class="bg-emerald-600 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2.5 font-sans text-xs font-bold border border-emerald-400/30">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span id="pos-toast-msg">Order berhasil diproses!</span>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();

            // Keyboard Shortcut Listener (UIX-11.1)
            document.addEventListener('keydown', (e) => {
                // Press '/' to focus search input
                if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'TEXTAREA') {
                    e.preventDefault();
                    const searchInput = document.getElementById('pos-search-input');
                    if (searchInput) {
                        searchInput.focus();
                        searchInput.select();
                    }
                }
                // Press 'Escape' to blur search
                if (e.key === 'Escape') {
                    const searchInput = document.getElementById('pos-search-input');
                    if (document.activeElement === searchInput) {
                        searchInput.blur();
                    }
                }
            });
        });

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('checkout:success', data => {
                const orderNum = (data && typeof data === 'object') ? (data.order_number || (data[0] && data[0].order_number)) : data;
                const toast = document.getElementById('pos-toast');
                const msg = document.getElementById('pos-toast-msg');
                if (toast && msg && orderNum) {
                    msg.textContent = 'Order #' + orderNum + ' berhasil dibuat!';
                    toast.classList.remove('translate-y-20', 'opacity-0', 'pointer-events-none');
                    setTimeout(() => {
                        toast.classList.add('translate-y-20', 'opacity-0', 'pointer-events-none');
                    }, 4000);
                }
                if (window.lucide) lucide.createIcons();
            });
        });
    </script>
</div>

