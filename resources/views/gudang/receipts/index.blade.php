@extends('layouts.app')

@section('title', 'Riwayat Barang Masuk - Gudang')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-truck text-info me-2"></i>Penerimaan Barang Masuk</h4>
        <p class="text-muted small mb-0">Catatan faktur penerimaan pasokan barang dari supplier ke gudang.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('gudang.receipts.create') }}" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-1"></i> Catat Penerimaan Baru
        </a>
    </div>
</div>

<!-- Filter Tanggal & Supplier -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('gudang.receipts.index') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-3">
                <label class="small text-muted fw-semibold">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>
            <div class="col-6 col-md-3">
                <label class="small text-muted fw-semibold">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>
            <div class="col-12 col-md-4">
                <label class="small text-muted fw-semibold">Pemasok (Supplier)</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">Semua Pemasok</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end gap-2 pt-md-3">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('gudang.receipts.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
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
                        <th>Nomor Penerimaan</th>
                        <th>Tanggal Terima</th>
                        <th>Supplier</th>
                        <th>Petugas Penerima</th>
                        <th class="text-center">Jumlah Item</th>
                        <th>Catatan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $r)
                        <tr>
                            <td>
                                <a href="{{ route('gudang.receipts.show', $r) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $r->receipt_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $r->received_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $r->received_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $r->supplier->name ?? 'Tanpa Supplier' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $r->user->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold">
                                    {{ $r->items->count() }} jenis barang ({{ $r->items->sum('quantity') }} unit)
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($r->notes, 40) ?: '-' }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('gudang.receipts.show', $r) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Belum ada catatan penerimaan barang masuk.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($receipts->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $receipts->links() }}
        </div>
    @endif
</div>
@endsection
