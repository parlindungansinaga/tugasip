<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Posisi Stok & Nilai Persediaan - Toko Retail Modern</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 12px; color: #000; background: #fff; }
        .table th, .table td { padding: 6px 8px; border-color: #000; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4" onload="window.print()">

<div class="no-print mb-3 text-end">
    <button onclick="window.print()" class="btn btn-primary btn-sm">🖨️ Cetak</button>
    <button onclick="window.close()" class="btn btn-secondary btn-sm">Tutup</button>
</div>

<div class="text-center mb-4 pb-2 border-bottom">
    <h3 class="fw-bold mb-0">TOKO RETAIL MODERN</h3>
    <p class="mb-1">Jl. Sukses Makmur No. 88, Jakarta Selatan - Telp: 021-5551234</p>
    <h5 class="fw-bold mt-2">LAPORAN POSISI STOK & NILAI PERSEDIAAN BARANG</h5>
    <small class="text-muted">Per Tanggal: {{ now()->translatedFormat('l, d F Y H:i') }} WIB</small>
</div>

<table class="table table-bordered mb-4">
    <thead>
        <tr class="table-light">
            <th>No</th>
            <th>Kode SKU</th>
            <th>Barcode</th>
            <th>Nama Barang</th>
            <th>Kategori</th>
            <th class="text-center">Stok</th>
            <th class="text-center">Min</th>
            <th class="text-end">Harga Beli (Rp)</th>
            <th class="text-end">Harga Jual (Rp)</th>
            <th class="text-end">Total Nilai Aset (Rp)</th>
            <th class="text-center">Kondisi</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandUnits = 0;
            $grandAsset = 0;
        @endphp
        @forelse($products as $idx => $p)
            @php
                $lineAsset = $p->stock * $p->purchase_price;
                $grandUnits += $p->stock;
                $grandAsset += $lineAsset;
                $status = $p->stock <= 0 ? 'HABIS' : ($p->isLowStock() ? 'MENIPIS' : 'NORMAL');
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace">{{ $p->sku }}</td>
                <td class="font-monospace">{{ $p->barcode ?: '-' }}</td>
                <td class="fw-bold">{{ $p->name }}</td>
                <td>{{ $p->category->name ?? '-' }}</td>
                <td class="text-center fw-bold">{{ $p->stock }} {{ $p->unit }}</td>
                <td class="text-center">{{ $p->minimum_stock }}</td>
                <td class="text-end">{{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($p->selling_price, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">{{ number_format($lineAsset, 0, ',', '.') }}</td>
                <td class="text-center">{{ $status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="11" class="text-center py-3">Tidak ada data.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="table-light fw-bold">
            <td colspan="5" class="text-end">TOTAL KESELURUHAN:</td>
            <td class="text-center">{{ number_format($grandUnits) }} unit</td>
            <td colspan="3"></td>
            <td class="text-end">Rp {{ number_format($grandAsset, 0, ',', '.') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="row mt-5 pt-3">
    <div class="col-6 text-center">
        <p class="mb-5">Mengetahui,<br><strong>Kepala Gudang & Toko</strong></p>
        <p>( ................................................. )</p>
    </div>
    <div class="col-6 text-center">
        <p class="mb-5">Dicetak pada {{ now()->format('d/m/Y H:i') }} WIB<br><strong>Administrator</strong></p>
        <p>( {{ Auth::user()->name }} )</p>
    </div>
</div>

</body>
</html>
