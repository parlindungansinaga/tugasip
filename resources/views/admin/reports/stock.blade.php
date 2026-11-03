@extends('layouts.app')

@section('title', 'Laporan Posisi Stok & Nilai Persediaan - Admin')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-info me-2"></i>Laporan Posisi Stok & Nilai Persediaan</h4>
        <p class="text-muted small mb-0">Valuasi aset persediaan barang berdasarkan harga pokok pembelian (HPP).</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
        <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV
        </a>
        <a href="{{ request()->fullUrlWithQuery(['print' => 1]) }}" target="_blank" class="btn btn-outline-dark btn-sm rounded-pill px-3">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
    </div>
</div>

<!-- Ringkasan Nilai Persediaan -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Jenis Produk (SKU)</small>
            <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($totalSku) }} <small class="fs-6 text-muted">SKU</small></h3>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Unit Fisik Gudang</small>
            <h3 class="fw-bold text-info mt-1 mb-0">{{ number_format($totalUnits) }} <small class="fs-6 text-muted">unit</small></h3>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3 border-primary border-opacity-50">
            <small class="text-muted text-uppercase fw-semibold">Total Valuasi Nilai Aset Stok (HPP)</small>
            <h3 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalAssetValue, 0, ',', '.') }}</h3>
        </div>
    </div>
</div>

<!-- Filter Kategori & Status Stok -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.stock') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Kondisi Stok</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>Stok Menipis (&le; Batas Minimum)</option>
                    <option value="out" {{ request('status') == 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('admin.reports.stock') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan Stok -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Kode SKU</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Batas Min</th>
                        <th class="text-end">Harga Beli (HPP)</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-end">Total Nilai Aset</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                        @php $lineAsset = $p->stock * $p->purchase_price; @endphp
                        <tr>
                            <td><span class="font-monospace fw-semibold">{{ $p->sku }}</span></td>
                            <td><small class="font-monospace text-muted">{{ $p->barcode ?: '-' }}</small></td>
                            <td><strong class="text-dark">{{ $p->name }}</strong></td>
                            <td><span class="badge bg-light text-secondary border">{{ $p->category->name ?? '-' }}</span></td>
                            <td class="text-center fw-bold {{ $p->stock <= 0 ? 'text-danger' : ($p->isLowStock() ? 'text-warning' : 'text-dark') }}">
                                {{ $p->stock }} {{ $p->unit }}
                            </td>
                            <td class="text-center text-muted">{{ $p->minimum_stock }} {{ $p->unit }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($p->purchase_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold">Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($lineAsset, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($p->stock <= 0)
                                    <span class="badge bg-danger">Habis</span>
                                @elseif($p->isLowStock())
                                    <span class="badge bg-warning text-dark">Menipis</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Tidak ada data produk yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection
