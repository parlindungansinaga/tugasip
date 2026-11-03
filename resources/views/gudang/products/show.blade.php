@extends('layouts.app')

@section('title', 'Detail Barang - ' . $product->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-info me-2"></i>{{ $product->name }}</h4>
        <p class="text-muted small mb-0">Informasi spesifikasi barang dan riwayat kartu mutasi stok.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('gudang.products.edit', $product) }}" class="btn btn-outline-warning btn-sm rounded-pill px-3">
            <i class="bi bi-pencil-square me-1"></i> Edit Data
        </a>
        <a href="{{ route('gudang.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Spesifikasi Master</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 40%;">Kode SKU</td>
                    <td class="fw-bold font-monospace">{{ $product->sku }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Barcode</td>
                    <td class="font-monospace">{{ $product->barcode ?: '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Kategori</td>
                    <td><span class="badge bg-light text-secondary border">{{ $product->category->name ?? '-' }}</span></td>
                </tr>
                <tr>
                    <td class="text-muted">Satuan</td>
                    <td>{{ $product->unit }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Jual</td>
                    <td>
                        @if($product->is_active)
                            <span class="badge bg-success rounded-pill">Aktif Dijual</span>
                        @else
                            <span class="badge bg-secondary rounded-pill">Nonaktif</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Struktur Harga</h6>
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 45%;">Harga Pokok (HPP)</td>
                    <td class="fw-semibold">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Harga Jual Kasir</td>
                    <td class="fw-bold text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Margin Keuntungan</td>
                    @php
                        $margin = $product->selling_price - $product->purchase_price;
                        $percent = $product->purchase_price > 0 ? ($margin / $product->purchase_price) * 100 : 0;
                    @endphp
                    <td class="fw-bold text-success">
                        Rp {{ number_format($margin, 0, ',', '.') }}
                        <small class="text-muted">({{ number_format($percent, 1) }}%)</small>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <h6 class="text-muted small fw-semibold text-uppercase mb-3">Status Persediaan Fisik</h6>
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div>
                    <h2 class="fw-bold mb-0 {{ $product->stock <= 0 ? 'text-danger' : ($product->isLowStock() ? 'text-warning' : 'text-success') }}">
                        {{ $product->stock }} <small class="fs-6 text-muted">{{ $product->unit }}</small>
                    </h2>
                    <small class="text-muted">Batas Minimum: {{ $product->minimum_stock }} {{ $product->unit }}</small>
                </div>
                <div>
                    @if($product->stock <= 0)
                        <span class="badge bg-danger p-2"><i class="bi bi-x-circle me-1"></i> Stok Habis</span>
                    @elseif($product->isLowStock())
                        <span class="badge bg-warning text-dark p-2"><i class="bi bi-exclamation-triangle me-1"></i> Menipis</span>
                    @else
                        <span class="badge bg-success p-2"><i class="bi bi-check-circle me-1"></i> Stok Aman</span>
                    @endif
                </div>
            </div>
            <div class="mt-auto pt-2 border-top">
                <small class="text-muted">Total Nilai Persediaan: </small>
                <strong class="text-dark">Rp {{ number_format($product->stock * $product->purchase_price, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Mutasi Stok Khusus Barang Ini -->
<div class="card card-custom">
    <div class="card-header bg-white border-0 py-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-1"></i> Kartu Riwayat Mutasi Stok</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jenis Mutasi</th>
                        <th>Keterangan / Referensi</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">Stok Sebelum</th>
                        <th class="text-center">Stok Sesudah</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $m->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $m->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                @if($m->movement_type === 'masuk')
                                    <span class="badge bg-success rounded-pill px-2">Barang Masuk</span>
                                @elseif($m->movement_type === 'keluar')
                                    <span class="badge bg-danger rounded-pill px-2">Penjualan Kasir</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2">Penyesuaian</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark">{{ $m->notes ?: '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if($m->quantity_in > 0)
                                    <span class="text-success fw-bold">+{{ $m->quantity_in }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($m->quantity_out > 0)
                                    <span class="text-danger fw-bold">-{{ $m->quantity_out }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center text-muted">{{ $m->stock_before }}</td>
                            <td class="text-center fw-bold text-dark">{{ $m->stock_after }}</td>
                            <td><small class="text-secondary">{{ $m->user->name ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat pergerakan stok untuk produk ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movements->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $movements->links() }}
        </div>
    @endif
</div>
@endsection
