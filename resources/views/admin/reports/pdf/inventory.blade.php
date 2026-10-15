<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background: #f4f4f4; text-align: left; }
        h1 { font-size: 20px; margin-bottom: 0.5rem; }
        .summary-item { margin-bottom: 0.75rem; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p>Periode: {{ $filters['start']->format('d M Y') }} – {{ $filters['end']->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                @if($section === 'raw_material')
                    <th>Bahan Baku</th>
                    <th>Qty Digunakan</th>
                    <th>Total Biaya</th>
                    <th>Biaya / Unit</th>
                @elseif($section === 'hpp_margin')
                    <th>Produk</th>
                    <th>Ukuran</th>
                    <th>Harga Jual</th>
                    <th>HPP</th>
                    <th>Margin Rp</th>
                    <th>Margin %</th>
                @else
                    <th>Sesi ID</th>
                    <th>Kasir</th>
                    <th>Dibuka</th>
                    <th>Ditutup</th>
                    <th>Saldo Awal</th>
                    <th>Saldo Akhir</th>
                    <th>Total Penjualan</th>
                    <th>Jumlah Pesanan</th>
                    <th>Selisih</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
