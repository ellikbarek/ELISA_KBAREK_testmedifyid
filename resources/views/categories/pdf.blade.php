<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kategori Items</title>
    <style>
        @page { margin: 35px 35px 65px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 7px; word-wrap: break-word; }
        th { background: #eee; text-align: left; }
        tr { page-break-inside: avoid; }
        thead { display: table-header-group; }
        footer { position: fixed; bottom: -40px; left: 0; right: 0; font-size: 10px; border-top: 1px solid #ccc; padding-top: 8px; }
    </style>
</head>
<body>
    <footer>Dicetak pada: {{ $printedAt }}</footer>
    <h2>Kategori Items</h2>
    <p><strong>Nama kategori:</strong> {{ $category->nama }}<br><strong>Kode kategori:</strong> {{ $category->kode }}</p>
    <table>
        <thead><tr><th>No</th><th>Kode</th><th>Nama Item</th><th>Supplier</th><th>Harga Beli</th><th>Laba (%)</th><th>Harga Jual</th></tr></thead>
        <tbody>
        @forelse($category->items as $item)
            <tr><td>{{ $loop->iteration }}</td><td>{{ $item->kode }}</td><td>{{ $item->nama }}</td><td>{{ $item->supplier }}</td><td>{{ number_format($item->harga_beli, 0, ',', '.') }}</td><td>{{ $item->laba }}</td><td>{{ number_format($item->harga_jual, 0, ',', '.') }}</td></tr>
        @empty
            <tr><td colspan="7">Belum ada item dalam kategori ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
