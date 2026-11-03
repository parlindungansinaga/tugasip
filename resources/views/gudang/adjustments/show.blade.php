@extends('layouts.app')

@section('title', 'Detail Penyesuaian Stok - ' . $adjustment->adjustment_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-file-earmark-diff text-info me-2"></i>{{ $adjustment->adjustment_number }}</h4>
        <p class="text-muted small mb-0">Rincian hasil koreksi dan penyesuaian stok opname fisik.</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-3 no-print">
            <i class="bi bi-printer me-1"></i> Cetak Dokumen
        </button>
        <a href="{{ route('gudang.adjustments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 no-print">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-md-4">
                <small class="text-muted text-uppercase fw-semibold d-block">Nomor Dokumen</small>
                <strong class="font-monospace text-primary fs-6">{{ $adjustment->adjustment_number }}</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted text-uppercase fw-semibold d-block">Waktu Penyesuaian</small>
                <strong>{{ $adjustment->adjusted_at->format('d/m/Y H:i') }} WIB</strong>
            </div>
            <div class="col-md-4">
                <small class="text-muted text-uppercase fw-semibold d-block">Petugas Pelaksana</small>
                <span class="badge bg-light text-secondary border">{{ $adjustment->user->name ?? '-' }}</span>
            </div>
            <div class="col-12 mt-2 pt-2 border-top">
                <small class="text-muted text-uppercase fw-semibold d-block">Catatan Dokumen</small>
                <span>{{ $adjustment->notes ?: 'Tidak ada catatan tambahan.' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-boxes me-1"></i> Daftar Barang yang Disesuaikan</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode SKU</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Stok Sebelum</th>
                        <th class="text-center">Stok Fisik Baru</th>
                        <th class="text-center">Selisih</th>
                        <th>Alasan Koreksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($adjustment->items as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><span class="font-monospace">{{ $item->product->sku ?? '-' }}</span></td>
                            <td><strong class="text-dark">{{ $item->product->name ?? 'Produk' }}</strong></td>
                            <td class="text-center text-muted">{{ $item->stock_before }} {{ $item->product->unit ?? 'pcs' }}</td>
                            <td class="text-center fw-bold text-dark">{{ $item->stock_after }} {{ $item->product->unit ?? 'pcs' }}</td>
                            <td class="text-center">
                                @if($item->difference > 0)
                                    <span class="badge bg-success">+{{ $item->difference }}</span>
                                @elseif($item->difference < 0)
                                    <span class="badge bg-danger">{{ $item->difference }}</span>
                                @else
                                    <span class="badge bg-secondary">0</span>
                                @endif
                            </td>
                            <td><span class="text-secondary small">{{ $item->reason }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
