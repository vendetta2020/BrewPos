<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $order->order_number }} — BrewPOS</title>
    <style>
        :root {
            --bg-canvas: #0E0D0D;
            --surface-card: #181716;
            --border-default: #2A2724;
            --text-primary: #F5F5F4;
            --text-secondary: #A8A29E;
            --brand-primary: #D97706;
            --danger: #EF4444;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: var(--bg-canvas);
            color: #111;
            font-family: 'Courier New', Courier, ui-monospace, monospace;
        }
        .screen-container {
            max-width: 420px;
            margin: 0 auto;
            padding: 24px 16px 48px;
        }
        .receipt-card {
            background: #ffffff;
            color: #111111;
            width: 80mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 16px 14px;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
            font-size: 12px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #444; margin: 10px 0; }
        .flex { display: flex; justify-content: space-between; }
        
        .actions-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 20px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            text-decoration: none;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            padding: 10px 16px;
            transition: all 0.15s ease;
        }
        .btn-print {
            background: #D97706;
            color: #FFFFFF;
            border: 1px solid #D97706;
        }
        .btn-print:hover {
            background: #B45309;
            border-color: #B45309;
        }
        .btn-void {
            background: rgba(239, 68, 68, 0.1);
            color: #F87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .btn-void:hover {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.5);
        }
        .btn-back {
            background: #1C1917;
            color: #D6D3D1;
            border: 1px solid #2E2A27;
        }
        .btn-back:hover {
            background: #262320;
            color: #FFFFFF;
            border-color: #3E3835;
        }
        .alert {
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13px;
        }
        .alert-success { 
            background: rgba(16, 185, 129, 0.1); 
            color: #34D399; 
            border: 1px solid rgba(16, 185, 129, 0.3); 
        }
        .alert-error { 
            background: rgba(239, 68, 68, 0.1); 
            color: #F87171; 
            border: 1px solid rgba(239, 68, 68, 0.3); 
        }
        .badge-cancelled {
            display: block;
            text-align: center;
            background: #FEE2E2;
            color: #991B1B;
            font-weight: 800;
            padding: 6px;
            border: 1px dashed #EF4444;
            border-radius: 4px;
            margin: 10px 0;
            letter-spacing: 1px;
            font-size: 11px;
        }
        /* Modal backdrop & container */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(2px);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal-box {
            background: #181716;
            border: 1px solid #2A2724;
            border-radius: 8px;
            max-width: 360px;
            width: 100%;
            padding: 20px;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.6);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #F5F5F4;
        }
        @media print {
            .no-print { display: none !important; }
            html, body {
                background: #FFFFFF !important;
                color: #111111 !important;
            }
            .screen-container {
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
            .receipt-card {
                box-shadow: none !important;
                border-radius: 0 !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="screen-container">
        <div class="no-print actions-group">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif

            <button onclick="window.print()" class="btn-action btn-print">Cetak Struk (Print Receipt)</button>

            @if($order->status !== 'cancelled')
                <button onclick="document.getElementById('voidModal').style.display='flex'" class="btn-action btn-void">
                    Batalkan Pesanan (Void Order)
                </button>
            @endif

            <a href="{{ route('pos') }}" class="btn-action btn-back">Kembali ke POS</a>
        </div>

        <div class="receipt-card">
            @if($order->status === 'cancelled')
                <div class="badge-cancelled">
                    PESANAN TELAH DIBATALKAN (VOID)
                </div>
            @endif

            <div class="text-center">
                <h2 class="bold" style="margin:0; font-size:16px; letter-spacing: 1px;">BREWPOS COFFEE</h2>
                <p style="margin:2px 0; font-size:11px;">Jl. Kopi Harapan No. 88, Jakarta</p>
                <p style="margin:2px 0; font-size:11px;">Telp: (021) 555-1234</p>
            </div>

            <div class="divider"></div>

            <div>
                <div class="flex"><span>No. Order:</span><span class="bold">{{ $order->order_number }}</span></div>
                <div class="flex"><span>Tanggal:</span><span>{{ $order->created_at->format('d/m/Y H:i') }}</span></div>
                <div class="flex"><span>Kasir:</span><span>{{ $order->user->name }}</span></div>
                <div class="flex"><span>Metode:</span><span class="bold">{{ strtoupper($order->payment_method) }}</span></div>
            </div>

            <div class="divider"></div>

            <div>
                @foreach($order->orderItems as $item)
                    <div style="margin-bottom: 6px;">
                        <div class="bold">{{ $item->product->name ?? 'Produk' }} ({{ $item->size }})</div>
                        <div class="flex">
                            <span>{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if($item->notes)
                            <div style="font-size:10px; font-style:italic; color:#555;">* {{ $item->notes }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="divider"></div>

            <div>
                <div class="flex"><span>Subtotal:</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                @if($order->discount > 0)
                    <div class="flex"><span>Diskon:</span><span>-Rp {{ number_format($order->discount, 0, ',', '.') }}</span></div>
                @endif
                @if($order->tax > 0)
                    <div class="flex"><span>Pajak (10%):</span><span>Rp {{ number_format($order->tax, 0, ',', '.') }}</span></div>
                @endif
                <div class="flex bold" style="font-size: 14px; margin-top: 6px;">
                    <span>TOTAL:</span>
                    <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>

                @if($order->payment_method === 'cash')
                    <div class="flex" style="margin-top:4px;"><span>Tunai:</span><span>Rp {{ number_format($order->cash_received, 0, ',', '.') }}</span></div>
                    <div class="flex"><span>Kembalian:</span><span>Rp {{ number_format($order->change_given, 0, ',', '.') }}</span></div>
                @endif
            </div>

            <div class="divider"></div>

            <div class="text-center" style="margin-top: 12px;">
                <p class="bold" style="margin: 2px 0;">Terima Kasih Atas Kunjungan Anda!</p>
                <p style="margin: 2px 0; font-size:10px; color:#555;">Simpan struk ini sebagai bukti pembayaran sah.</p>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal for Void Order -->
    <div id="voidModal" class="modal-overlay no-print">
        <div class="modal-box">
            <h3 style="margin-top:0; color:#EF4444; font-size:15px; font-weight:700;">Konfirmasi Pembatalan</h3>
            <p style="font-size:12px; color:#A8A29E; line-height:1.4; margin-bottom: 12px;">
                Apakah Anda yakin ingin membatalkan pesanan <strong>#{{ $order->order_number }}</strong>? Stok bahan baku akan otomatis dikembalikan ke inventaris.
            </p>
            <form method="POST" action="{{ route('cashier.orders.void', $order) }}">
                @csrf
                <label style="display:block; font-size:12px; font-weight:600; color:#D6D3D1; margin-bottom:6px;">Alasan Pembatalan:</label>
                <textarea name="reason" rows="2" required placeholder="Contoh: Salah input pesanan / pelanggan batal" style="width:100%; box-sizing:border-box; font-family:inherit; font-size:12px; background:#121110; color:#F5F5F4; border:1px solid #2E2A27; border-radius:6px; padding:8px; margin-bottom:14px; resize:none;"></textarea>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="document.getElementById('voidModal').style.display='none'" style="flex:1; padding:8px 12px; background:#262320; color:#D6D3D1; border:1px solid #2E2A27; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="flex:1; padding:8px 12px; background:#EF4444; color:#FFFFFF; border:none; border-radius:6px; font-weight:700; font-size:12px; cursor:pointer;">
                        Ya, Void Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

