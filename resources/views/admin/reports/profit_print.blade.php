<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuntungan Kotor - Toko Retail Modern</title>
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
    <h5 class="fw-bold mt-2">LAPORAN ESTIMASI KEUNTUNGAN KOTOR (GROSS PROFIT)</h5>
    <small class="text-muted">Periode: {{ $startDate ?: 'Semua' }} s/d {{ $endDate ?: 'Semua' }}</small>
</div>

<table class="table table-bordered mb-4">
    <thead>
        <tr class="table-light">
            <th>No</th>
            <th>No. Faktur</th>
            <th>Waktu</th>
            <th>Kasir</th>
            <th class="text-end">Total Penjualan (Rp)</th>
            <th class="text-end">Total HPP Modal (Rp)</th>
            <th class="text-end">Keuntungan Kotor (Rp)</th>
            <th class="text-center">Margin</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandSales = 0;
            $grandHpp = 0;
            $grandProfit = 0;
        @endphp
        @forelse($sales as $idx => $s)
            @php
                $lineHpp = 0;
                foreach ($s->items as $item) {
                    $lineHpp += ($item->purchase_price_snapshot * $item->quantity);
                }
                $lineProfit = $s->total_amount - $lineHpp;
                $lineMargin = $s->total_amount > 0 ? ($lineProfit / $s->total_amount) * 100 : 0;

                $grandSales += $s->total_amount;
                $grandHpp += $lineHpp;
                $grandProfit += $lineProfit;
            @endphp
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td class="font-monospace fw-bold">{{ $s->invoice_number }}</td>
                <td>{{ $s->sold_at->format('d/m/Y H:i') }}</td>
                <td>{{ $s->user->name ?? '-' }}</td>
                <td class="text-end">{{ number_format($s->total_amount, 0, ',', '.') }}</td>
                <td class="text-end">{{ number_format($lineHpp, 0, ',', '.') }}</td>
                <td class="text-end fw-bold">{{ number_format($lineProfit, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($lineMargin, 1) }}%</td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-3">Tidak ada data.</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot>
        <tr class="table-light fw-bold">
            <td colspan="4" class="text-end">TOTAL KESELURUHAN:</td>
            <td class="text-end">Rp {{ number_format($grandSales, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($grandHpp, 0, ',', '.') }}</td>
            <td class="text-end">Rp {{ number_format($grandProfit, 0, ',', '.') }}</td>
            <td class="text-center">{{ $grandSales > 0 ? number_format(($grandProfit / $grandSales) * 100, 1) : 0 }}%</td>
        </tr>
    </tfoot>
</table>

<div class="row mt-5 pt-3">
    <div class="col-6 text-center">
        <p class="mb-5">Mengetahui,<br><strong>Pimpinan Toko</strong></p>
        <p>( ................................................. )</p>
    </div>
    <div class="col-6 text-center">
        <p class="mb-5">Dicetak pada {{ now()->format('d/m/Y H:i') }} WIB<br><strong>Administrator</strong></p>
        <p>( {{ Auth::user()->name }} )</p>
    </div>
</div>

</body>
</html>
