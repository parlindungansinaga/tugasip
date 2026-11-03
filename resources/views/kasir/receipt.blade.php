<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan - {{ $sale->invoice_number }}</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            background-color: #f1f5f9;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        .receipt-container {
            width: 78mm;
            background: #ffffff;
            padding: 15px 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            font-size: 12px;
            color: #000;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .store-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .store-sub {
            font-size: 11px;
            margin-bottom: 6px;
        }

        .meta-table, .items-table, .total-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            font-size: 11px;
            padding: 2px 0;
        }

        .items-table th {
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            font-size: 11px;
        }

        .items-table td {
            padding: 4px 0;
            vertical-align: top;
            font-size: 11px;
        }

        .total-table td {
            padding: 3px 0;
            font-size: 12px;
        }

        .btn-print-box {
            margin-bottom: 15px;
            text-align: center;
        }

        .btn-action {
            background-color: #2563eb;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
            margin: 0 4px;
        }

        .btn-back {
            background-color: #64748b;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            @page {
                margin: 0;
                size: auto;
            }
        }
    </style>
</head>
<body>

<div>
    <!-- Tombol Cetak / Navigasi (Hilang saat cetak) -->
    <div class="no-print btn-print-box">
        <button class="btn-action" onclick="window.print()">
            🖨️ Cetak Struk (Print)
        </button>
        <button class="btn-action btn-back" onclick="window.close(); history.back();">
            ⬅️ Kembali
        </button>
    </div>

    <!-- Kontainer Struk Thermal -->
    <div class="receipt-container">
        <div class="text-center">
            <div class="store-title">TOKO RETAIL MODERN</div>
            <div class="store-sub">Jl. Sukses Makmur No. 88, Jakarta<br>Telp: 021-5551234</div>
        </div>

        <div class="divider"></div>

        <table class="meta-table">
            <tr>
                <td>No. Faktur</td>
                <td class="text-end fw-bold">{{ $sale->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td class="text-end">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td class="text-end">{{ $sale->user->name ?? 'Kasir' }}</td>
            </tr>
            <tr>
                <td>Pembayaran</td>
                <td class="text-end fw-bold">{{ strtoupper($sale->payment_method) }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%; text-align: left;">Barang</th>
                    <th style="width: 20%;" class="text-center">Qty</th>
                    <th style="width: 30%;" class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td colspan="3" class="fw-bold" style="padding-top: 5px;">{{ $item->product_name_snapshot }}</td>
                    </tr>
                    <tr>
                        <td style="font-size: 10px; color: #444;">{{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td class="text-center" style="font-size: 10px;">x {{ $item->quantity }}</td>
                        <td class="text-end" style="font-weight: bold;">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <table class="total-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($sale->discount_amount > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="text-end">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr class="fw-bold" style="font-size: 14px;">
                <td>TOTAL</td>
                <td class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar Tunai</td>
                <td class="text-end">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr class="fw-bold">
                <td>Kembalian</td>
                <td class="text-end">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="text-center" style="margin-top: 8px; font-size: 11px;">
            <p>Terima kasih atas kunjungan Anda!</p>
            <p style="font-size: 10px; color: #555; margin-top: 3px;">Barang yang sudah dibeli tidak dapat ditukar/dikembalikan.</p>
        </div>
    </div>
</div>

<script>
    // Otomatis buka dialog print saat struk dibuka dari transaksi baru
    window.onload = function() {
        if (window.location.search.includes('autoprint=1')) {
            window.print();
        }
    };
</script>
</body>
</html>
