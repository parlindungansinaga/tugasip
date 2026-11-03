@extends('layouts.app')

@section('title', 'Detail Penerimaan Barang - ' . $receipt->receipt_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-check text-info me-2"></i>{{ $receipt->receipt_number }}</h4>
        <p class="text-muted small mb-0">Rincian faktur penerimaan barang masuk ke gudang.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-3 no-print">
            <i class="bi bi-printer me-1"></i> Cetak Bukti
        </button>
        <a href="{{ route('gudang.receipts.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 no-print">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Informasi Faktur</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 40%;">Nomor Faktur</td>
                    <td class="fw-bold font-monospace text-primary">{{ $receipt->receipt_number }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Tanggal Terima</td>
                    <td>{{ $receipt->received_at->format('d/m/Y H:i') }} WIB</td>
                </tr>
                <tr>
                    <td class="text-muted">Petugas Penerima</td>
                    <td><span class="badge bg-light text-secondary border">{{ $receipt->user->name ?? '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Catatan</td>
                    <td>{{ $receipt->notes ?: '-' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Informasi Supplier</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 40%;">Nama Pemasok</td>
                    <td class="fw-bold">{{ $receipt->supplier->name ?? 'Pembelian Langsung / Tanpa Supplier' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">No. Telepon</td>
                    <td>{{ $receipt->supplier->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Alamat</td>
                    <td><small>{{ $receipt->supplier->address ?? '-' }}</small></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-boxes me-1"></i> Daftar Barang Diterima</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Jumlah Diterima</th>
                        <th class="text-end">Harga Beli Satuan</th>
                        <th class="text-end">Total Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @foreach($receipt->items as $idx => $item)
                        @php 
                            $lineTotal = $item->quantity * $item->purchase_price;
                            $grandTotal += $lineTotal;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><span class="font-monospace fw-semibold">{{ $item->product->sku ?? '-' }}</span></td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $item->product->name ?? 'Produk' }}</span>
                            </td>
                            <td class="text-center fw-bold text-success">
                                +{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}
                            </td>
                            <td class="text-end">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($lineTotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <th colspan="3" class="text-end">TOTAL KESELURUHAN:</th>
                        <th class="text-center fw-bold text-success">{{ $receipt->items->sum('quantity') }} unit</th>
                        <th></th>
                        <th class="text-end fw-bold text-dark fs-6">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
