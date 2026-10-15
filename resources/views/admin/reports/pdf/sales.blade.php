<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        h1 { font-size: 20px; margin-bottom: 0.5rem; }
        .summary-grid { margin-top: 1rem; }
        .summary-item { margin-bottom: 0.75rem; }
    </style>
</head>
<body>
    <h1>Laporan Penjualan</h1>
    <p>Periode: {{ $filters['start']->format('d M Y') }} – {{ $filters['end']->format('d M Y') }}</p>

    <div class="summary-grid">
        <div class="summary-item"><strong>Pendapatan:</strong> Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Transaksi:</strong> {{ $summary['transaction_count'] }}</div>
        <div class="summary-item"><strong>AOV:</strong> Rp {{ number_format($summary['average_order_value'], 0, ',', '.') }}</div>
        <div class="summary-item"><strong>Stok Kritis:</strong> {{ $summary['critical_stock_count'] }}</div>
    </div>

    <h2>Produk Terlaris</h2>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Terjual</th>
                <th>Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Tidak diketahui' }}</td>
                    <td>{{ $item->quantity_sold }}</td>
                    <td>Rp {{ number_format($item->revenue, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
