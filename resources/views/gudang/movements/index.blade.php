@extends('layouts.app')

@section('title', 'Riwayat Mutasi Stok - Gudang')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-clock-history text-info me-2"></i>Riwayat Pergerakan Stok (Audit Trail)</h4>
        <p class="text-muted small mb-0">Buku besar kartu stok mencatat seluruh penambahan, pengurangan, dan penyesuaian.</p>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('gudang.movements.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" 
                       placeholder="Cari nama, SKU, barcode..." value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-2">
                <select name="movement_type" class="form-select form-select-sm">
                    <option value="">Semua Jenis Mutasi</option>
                    <option value="masuk" {{ request('movement_type') == 'masuk' ? 'selected' : '' }}>Masuk (Penerimaan)</option>
                    <option value="keluar" {{ request('movement_type') == 'keluar' ? 'selected' : '' }}>Keluar (Penjualan)</option>
                    <option value="penyesuaian" {{ request('movement_type') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian (Opname)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>
            <div class="col-6 col-md-2">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('gudang.movements.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Kode SKU</th>
                        <th>Nama Barang</th>
                        <th>Jenis Mutasi</th>
                        <th>Keterangan / Dokumen</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">Sebelum</th>
                        <th class="text-center">Sesudah</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $m)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $m->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $m->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td><span class="font-monospace fw-semibold">{{ $m->product->sku ?? '-' }}</span></td>
                            <td>
                                <strong class="text-dark">{{ $m->product->name ?? 'Barang' }}</strong>
                            </td>
                            <td>
                                @if($m->movement_type === 'masuk')
                                    <span class="badge bg-success rounded-pill px-2">Masuk</span>
                                @elseif($m->movement_type === 'keluar')
                                    <span class="badge bg-danger rounded-pill px-2">Keluar</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill px-2">Penyesuaian</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-secondary small">{{ $m->notes ?: '-' }}</span>
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
                            <td>
                                <small class="text-muted">{{ $m->user->name ?? '-' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Tidak ada data mutasi stok yang sesuai kriteria pencarian.
                            </td>
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
