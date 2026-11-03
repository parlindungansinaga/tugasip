@extends('layouts.app')

@section('title', 'Laporan Keuntungan Kotor - Admin')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-cash-stack text-success me-2"></i>Laporan Estimasi Keuntungan Kotor</h4>
        <p class="text-muted small mb-0">Analisis margin keuntungan kotor berdasarkan snapshot HPP saat transaksi terjadi.</p>
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

<!-- Ringkasan Finansial Laba Kotor -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Pendapatan Bersih</small>
            <h3 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
            <small class="text-muted">Total nilai penjualan transaksi</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Total Harga Pokok (HPP)</small>
            <h3 class="fw-bold text-secondary mt-1 mb-0">Rp {{ number_format($totalHpp, 0, ',', '.') }}</h3>
            <small class="text-muted">Berdasarkan snapshot modal beli</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom p-3 border-success border-opacity-50">
            <small class="text-muted text-uppercase fw-semibold">Estimasi Keuntungan Kotor</small>
            <h3 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h3>
            <small class="text-muted">Pendapatan Bersih - HPP</small>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom p-3">
            <small class="text-muted text-uppercase fw-semibold">Rata-rata Margin Kotor</small>
            <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($profitMargin, 1) }}%</h3>
            <small class="text-muted">Persentase laba kotor terhadap omzet</small>
        </div>
    </div>
</div>

<div class="alert alert-light border shadow-sm mb-4">
    <div class="d-flex align-items-center">
        <i class="bi bi-info-circle-fill text-primary fs-5 me-2"></i>
        <small class="text-muted">
            <strong>Catatan Akuntansi:</strong> Angka di atas merupakan <em>Keuntungan Kotor (Gross Profit)</em> yang dihitung dari: <code>Total Penjualan - Total HPP</code>. Nilai ini belum memperhitungkan biaya operasional (listrik, gaji kasir, sewa toko, atau beban pajak).
        </small>
    </div>
</div>

<!-- Filter Rentang Tanggal -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.reports.profit') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-4">
                <label class="small text-muted fw-semibold">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-6 col-md-4">
                <label class="small text-muted fw-semibold">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-6 col-md-2">
                <label class="small text-muted fw-semibold">Kasir</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c->id }}" {{ request('user_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex align-items-end gap-2 pt-md-3">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('admin.reports.profit') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Laporan Laba Kotor -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Waktu</th>
                        <th>Kasir</th>
                        <th class="text-end">Total Penjualan</th>
                        <th class="text-end">Total HPP Modal</th>
                        <th class="text-end">Keuntungan Kotor</th>
                        <th class="text-center">Margin</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        @php
                            $saleHpp = 0;
                            foreach ($sale->items as $item) {
                                $saleHpp += ($item->purchase_price_snapshot * $item->quantity);
                            }
                            $saleProfit = $sale->total_amount - $saleHpp;
                            $marginPercent = $sale->total_amount > 0 ? ($saleProfit / $sale->total_amount) * 100 : 0;
                        @endphp
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
                            <td class="text-end fw-semibold text-dark">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($saleHpp, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($saleProfit, 0, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                    {{ number_format($marginPercent, 1) }}%
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.reports.sales.detail', $sale) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Tidak ada data penjualan untuk perhitungan laba pada periode ini.
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
