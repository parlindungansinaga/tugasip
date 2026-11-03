@extends('layouts.app')

@section('title', 'Detail Faktur Penjualan - ' . $sale->invoice_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-primary me-2"></i>Faktur Penjualan: {{ $sale->invoice_number }}</h4>
        <p class="text-muted small mb-0">Rincian data transaksi penjualan tersimpan dalam database.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.print', $sale) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-printer me-1"></i> Cetak Struk Kasir
        </a>
        <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Informasi Transaksi</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 40%;">Nomor Faktur</td>
                    <td class="fw-bold font-monospace text-primary">{{ $sale->invoice_number }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Waktu Transaksi</td>
                    <td>{{ $sale->sold_at->format('d/m/Y H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="text-muted">Kasir Bertugas</td>
                    <td><span class="badge bg-light text-secondary border">{{ $sale->user->name ?? '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Metode Pembayaran</td>
                    <td><span class="badge bg-secondary bg-opacity-10 text-dark text-uppercase">{{ $sale->payment_method }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Status Transaksi</td>
                    <td><span class="badge bg-success rounded-pill px-2">Selesai / Lunas</span></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Ringkasan Finansial</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 45%;">Subtotal Penjualan</td>
                    <td>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Diskon Transaksi</td>
                    <td class="text-danger">-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted fw-bold">Total Pembayaran</td>
                    <td class="fw-bold text-dark fs-6">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Uang Diterima Kasir</td>
                    <td>Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Kembalian Diberikan</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Daftar Rincian Barang -->
<div class="card card-custom">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-cart-check me-1"></i> Rincian Barang Terjual</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode SKU (Snapshot)</th>
                        <th>Nama Produk (Snapshot)</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">HPP (Snapshot Modal)</th>
                        <th class="text-center">Jumlah Qty</th>
                        <th class="text-end">Subtotal Jual</th>
                        <th class="text-end">Laba Kotor Item</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $totalHpp = 0;
                        $totalItemProfit = 0;
                    @endphp
                    @foreach($sale->items as $idx => $item)
                        @php
                            $lineHpp = $item->purchase_price_snapshot * $item->quantity;
                            $lineProfit = $item->subtotal - $lineHpp;
                            $totalHpp += $lineHpp;
                            $totalItemProfit += $lineProfit;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><span class="font-monospace text-muted">{{ $item->sku_snapshot }}</span></td>
                            <td><strong class="text-dark">{{ $item->product_name_snapshot }}</strong></td>
                            <td class="text-end">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($item->purchase_price_snapshot, 0, ',', '.') }}</td>
                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($lineProfit, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="5" class="text-end">TOTAL:</th>
                        <th class="text-center fw-bold">{{ $sale->items->sum('quantity') }} unit</th>
                        <th class="text-end fw-bold fs-6">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
                        <th class="text-end fw-bold text-success fs-6">Rp {{ number_format($totalItemProfit, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
