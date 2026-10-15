<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran #{{ $order->order_number }} — BrewPOS</title>
    <style>
        body {
            font-family: 'Courier New', Courier, ui-monospace, monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 12px;
            color: #111;
            background: #fff;
            font-size: 12px;
            line-height: 1.4;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #444; margin: 10px 0; }
        .flex { display: flex; justify-content: space-between; }
        .btn-action {
            display: block;
            text-align: center;
            text-decoration: none;
            box-sizing: border-box;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 13px;
            font-weight: 700;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            padding: 10px 16px;
            transition: all 0.2s;
            margin-bottom: 8px;
        }
        .btn-print {
            background: #181716;
            color: #fff;
            border: 1px solid #333;
        }
        .btn-print:hover {
            background: #2D2A26;
        }
        .btn-void {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .btn-void:hover {
            background: #fee2e2;
        }
        .btn-back {
            background: #f4f4f5;
            color: #52525b;
            border: 1px solid #e4e4e7;
        }
        .btn-back:hover {
            background: #e4e4e7;
        }
        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 12px;
        }
        .alert-success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-cancelled {
            display: block;
            text-align: center;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 800;
            padding: 6px;
            border: 1px dashed #ef4444;
            border-radius: 6px;
            margin: 10px 0;
            letter-spacing: 1px;
        }
        /* Modal backdrop & container */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .modal-box {
            background: #fff;
            border-radius: 12px;
            max-width: 320px;
            width: 100%;
            padding: 18px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            font-family: system-ui, -apple-system, sans-serif;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
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

    <!-- Confirmation Modal for Void Order -->
    <div id="voidModal" class="modal-overlay no-print">
        <div class="modal-box">
            <h3 style="margin-top:0; color:#991b1b; font-size:15px; font-weight:700;">Konfirmasi Pembatalan</h3>
            <p style="font-size:12px; color:#4b5563; line-height:1.4;">
                Apakah Anda yakin ingin membatalkan pesanan <strong>#{{ $order->order_number }}</strong>? Stok bahan baku akan otomatis dikembalikan ke inventaris.
            </p>
            <form method="POST" action="{{ route('cashier.orders.void', $order) }}" style="margin-top:12px;">
                @csrf
                <label style="display:block; font-size:11px; font-weight:600; color:#374151; margin-bottom:4px;">Alasan Pembatalan:</label>
                <textarea name="reason" rows="2" required placeholder="Contoh: Salah input pesanan / pelanggan batal" style="width:100%; box-sizing:border-box; font-family:inherit; font-size:12px; border:1px solid #d1d5db; border-radius:6px; padding:6px; margin-bottom:12px;"></textarea>
                <div style="display:flex; gap:8px;">
                    <button type="button" onclick="document.getElementById('voidModal').style.display='none'" style="flex:1; padding:8px; background:#f3f4f6; border:1px solid #d1d5db; border-radius:6px; font-weight:600; font-size:12px; cursor:pointer;">
                        Batal
                    </button>
                    <button type="submit" style="flex:1; padding:8px; background:#dc2626; color:#fff; border:none; border-radius:6px; font-weight:700; font-size:12px; cursor:pointer;">
                        Ya, Void Order
                    </button>
                </div>
            </form>
        </div>
    </div>

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
</body>
</html>

