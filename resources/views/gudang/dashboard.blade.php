@extends('layouts.app')

@section('title', 'Dashboard Gudang & Manajemen Stok')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-box-seam text-info me-2"></i>Dashboard Gudang</h4>
        <p class="text-muted small mb-0">Pemantauan persediaan, penerimaan barang, dan pergerakan stok toko.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('gudang.receipts.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 me-2">
            <i class="bi bi-plus-circle me-1"></i> Catat Barang Masuk
        </a>
        <a href="{{ route('gudang.adjustments.create') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-sliders me-1"></i> Penyesuaian Stok
        </a>
    </div>
</div>

<!-- Statistik Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Barang Aktif</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($totalProducts) }}</h3>
                    <small class="text-muted">{{ number_format($totalUnits) }} total unit fisik</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-boxes fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3 border-warning border-opacity-50">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Stok Menipis</span>
                    <h3 class="fw-bold text-warning mt-1 mb-0">{{ number_format($lowStockCount) }}</h3>
                    <small class="text-muted">Perlu restock segera</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3 border-danger border-opacity-50">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Stok Habis (Kosong)</span>
                    <h3 class="fw-bold text-danger mt-1 mb-0">{{ number_format($outOfStockCount) }}</h3>
                    <small class="text-muted">Tidak dapat dijual di kasir</small>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle">
                    <i class="bi bi-x-octagon-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Barang Masuk (Bulan Ini)</span>
                    <h3 class="fw-bold text-info mt-1 mb-0">{{ number_format($receiptsThisMonth) }}</h3>
                    <small class="text-muted">{{ number_format($movementsThisMonth) }} kali aktivitas mutasi</small>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bi bi-truck fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Peringatan Barang Butuh Restock -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-danger">
                    <i class="bi bi-bell-fill me-1"></i> Peringatan Stok Menipis & Habis
                </h6>
                <a href="{{ route('gudang.products.index', ['stock_status' => 'low']) }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @if($restockAlerts->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-check-circle-fill text-success fs-3 mb-2 d-block"></i>
                        Semua stok barang dalam kondisi aman.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Barang</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Sisa Stok</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($restockAlerts as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $item->name }}</div>
                                            <small class="text-muted">{{ $item->sku }}</small>
                                        </td>
                                        <td><span class="badge bg-light text-secondary border">{{ $item->category->name ?? '-' }}</span></td>
                                        <td class="text-center fw-bold {{ $item->stock <= 0 ? 'text-danger' : 'text-warning' }}">
                                            {{ $item->stock }} {{ $item->unit }}
                                        </td>
                                        <td class="text-center">
                                            @if($item->stock <= 0)
                                                <span class="badge bg-danger rounded-pill px-2 py-1">Habis</span>
                                            @else
                                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Min: {{ $item->minimum_stock }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('gudang.receipts.create') }}" class="btn btn-sm btn-outline-primary" title="Tambah Stok Masuk">
                                                <i class="bi bi-plus-lg"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- 10 Mutasi Stok Terakhir -->
    <div class="col-lg-6">
        <div class="card card-custom h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">
                    <i class="bi bi-clock-history text-secondary me-1"></i> Aktivitas Mutasi Stok Terkini
                </h6>
                <a href="{{ route('gudang.movements.index') }}" class="small text-primary text-decoration-none">Semua Mutasi</a>
            </div>
            <div class="card-body p-0">
                @if($recentMovements->isEmpty())
                    <div class="text-center py-4 text-muted">Belum ada riwayat pergerakan stok.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Barang</th>
                                    <th class="text-center">Perubahan</th>
                                    <th class="text-center">Sisa</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentMovements as $mov)
                                    <tr>
                                        <td>
                                            <small class="text-muted d-block">{{ $mov->created_at->format('d/m/Y') }}</small>
                                            <small class="fw-semibold">{{ $mov->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $mov->product->name ?? 'Barang' }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($mov->movement_type === 'masuk')
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                                    +{{ $mov->quantity_in }}
                                                </span>
                                            @elseif($mov->movement_type === 'keluar')
                                                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold">
                                                    -{{ $mov->quantity_out }}
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-dark fw-bold">
                                                    Penyesuaian
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ $mov->stock_after }}
                                        </td>
                                        <td>
                                            <small class="text-secondary">{{ $mov->user->name ?? 'Sistem' }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
