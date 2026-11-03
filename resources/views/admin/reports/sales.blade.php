@extends('layouts.app')

@section('title', 'Laporan Penjualan - Admin')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Laporan Penjualan Toko</h4>
        <p class="text-muted small mb-0">Rincian pendapatan penjualan, volume transaksi, dan kinerja kasir.</p>
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

<!-- Ringkasan Statistik Laporan -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Pendapatan (Omzet)</small>
            <h3 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Transaksi Selesai</small>
            <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($totalTransactions) }} <small class="fs-6 text-muted">transaksi</small></h3>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Kuantitas Terjual</small>
            <h3 class="fw-bold text-success mt-1 mb-0">{{ number_format($totalUnits) }} <small class="fs-6 text-muted">unit barang</small></h3>
        </div>
    </div>
</div>

<!-- Filter Box -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.sales') }}">
            <!-- Preset Buttons -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="small fw-semibold text-muted align-self-center me-1">Periode Cepat:</span>
                <a href="{{ route('admin.reports.sales', ['preset' => 'today']) }}" 
                   class="btn btn-sm {{ $preset === 'today' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Hari Ini
                </a>
                <a href="{{ route('admin.reports.sales', ['preset' => 'yesterday']) }}" 
                   class="btn btn-sm {{ $preset === 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Kemarin
                </a>
                <a href="{{ route('admin.reports.sales', ['preset' => 'this_week']) }}" 
                   class="btn btn-sm {{ $preset === 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Minggu Ini
                </a>
                <a href="{{ route('admin.reports.sales', ['preset' => 'this_month']) }}" 
                   class="btn btn-sm {{ $preset === 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                    Bulan Ini
                </a>
            </div>

            <!-- Custom Filter Form -->
            <div class="row g-2 align-items-center">
                <input type="hidden" name="preset" value="custom">
                <div class="col-6 col-md-3">
                    <label class="small text-muted fw-semibold">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="small text-muted fw-semibold">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="small text-muted fw-semibold">Petugas Kasir</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">Semua Kasir</option>
                        @foreach($cashiers as $c)
                            <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex align-items-end gap-2 pt-3">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Terapkan Filter</button>
                    <a href="{{ route('admin.reports.sales') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan Penjualan -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th class="text-center">Jumlah Barang</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Diskon</th>
                        <th class="text-end">Total Akhir</th>
                        <th>Metode</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.sales.detail', $sale) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $sale->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $sale->sold_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $sale->sold_at->format('H:i') }} WIB</small>
                            </td>
                            <td><span class="badge bg-light text-secondary border">{{ $sale->user->name ?? '-' }}</span></td>
                            <td class="text-center fw-semibold">{{ $sale->items->sum('quantity') }} unit</td>
                            <td class="text-end text-muted">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                            <td class="text-end text-danger">{{ $sale->discount_amount > 0 ? '-Rp ' . number_format($sale->discount_amount, 0, ',', '.') : '-' }}</td>
                            <td class="text-end fw-bold text-dark">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-dark text-uppercase">{{ $sale->payment_method }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.reports.sales.detail', $sale) }}" class="btn btn-sm btn-outline-secondary" title="Lihat Rincian">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.print', $sale) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Cetak Struk">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Tidak ada data transaksi penjualan pada periode yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sales->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $sales->links() }}
        </div>
    @endif
</div>
@endsection
