@extends('layouts.app')

@section('title', 'Executive Dashboard - Admin Toko')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h4 class="fw-bold mb-1"><i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Utama Toko</h4>
        <p class="text-muted small mb-0">Ringkasan performa finansial harian, tren laba kotor, dan posisi stok toko.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <span class="badge bg-light text-dark border p-2">
            <i class="bi bi-calendar3 me-1 text-primary"></i> Hari ini: {{ now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
</div>

<!-- 6 Kartu Statistik Ringkasan Hari Ini -->
<div class="row g-3 mb-4">
    <!-- Omzet Penjualan Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Penjualan Hari Ini</span>
                    <h3 class="fw-bold text-primary mt-1 mb-0">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</h3>
                    <small class="text-muted">{{ number_format($transactionCountToday) }} transaksi selesai</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                    <i class="bi bi-currency-dollar fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Estimasi Keuntungan Kotor Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Estimasi Laba Kotor Hari Ini</span>
                    <h3 class="fw-bold text-success mt-1 mb-0">Rp {{ number_format($grossProfitToday, 0, ',', '.') }}</h3>
                    @php
                        $marginToday = $totalSalesToday > 0 ? ($grossProfitToday / $totalSalesToday) * 100 : 0;
                    @endphp
                    <small class="text-success fw-semibold">Margin {{ number_format($marginToday, 1) }}% di atas HPP</small>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                    <i class="bi bi-cash-stack fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Unit Barang Terjual Hari Ini -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Unit Terjual Hari Ini</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($unitsSoldToday) }} <small class="fs-6 text-muted">unit</small></h3>
                    <small class="text-muted">Total kuantitas barang fisik keluar</small>
                </div>
                <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                    <i class="bi bi-bag-check-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Produk Aktif -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Total Master Produk</span>
                    <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($activeProductsCount) }}</h3>
                    <small class="text-muted">Produk aktif dijual di kasir</small>
                </div>
                <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle">
                    <i class="bi bi-boxes fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Peringatan Stok Menipis -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3 {{ $lowStockCount > 0 ? 'border-warning' : '' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Barang Stok Menipis</span>
                    <h3 class="fw-bold {{ $lowStockCount > 0 ? 'text-warning' : 'text-dark' }} mt-1 mb-0">
                        {{ number_format($lowStockCount) }}
                    </h3>
                    <small class="text-muted">Stok &le; batas minimum</small>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Navigasi Cepat -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom h-100 p-3 d-flex flex-column justify-content-center">
            <span class="text-muted small fw-semibold text-uppercase mb-2">Akses Cepat Laporan</span>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.sales') }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                    <i class="bi bi-graph-up me-1"></i> Penjualan
                </a>
                <a href="{{ route('admin.reports.profit') }}" class="btn btn-sm btn-outline-success flex-grow-1">
                    <i class="bi bi-wallet2 me-1"></i> Laba Kotor
                </a>
                <a href="{{ route('admin.reports.stock') }}" class="btn btn-sm btn-outline-info flex-grow-1">
                    <i class="bi bi-box me-1"></i> Stok
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Interaktif Chart.js -->
<div class="row g-4 mb-4">
    <!-- Tren Penjualan & Laba 7 Hari Terakhir -->
    <div class="col-lg-8">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-activity text-primary me-1"></i> Tren Penjualan & Laba Kotor (7 Hari Terakhir)
                </h6>
                <span class="badge bg-light text-muted border">Chart.js</span>
            </div>
            <div style="position: relative; height: 280px;">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top 5 Produk Terlaris -->
    <div class="col-lg-4">
        <div class="card card-custom h-100 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-trophy-fill text-warning me-1"></i> Produk Terlaris Bulan Ini
                </h6>
                <small class="text-muted">Top 5</small>
            </div>
            @if($topProducts->isEmpty())
                <div class="text-center py-5 text-muted">Belum ada transaksi di bulan ini.</div>
            @else
                <div style="position: relative; height: 280px;">
                    <canvas id="topProductsChart"></canvas>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- 5 Transaksi Penjualan Terkini -->
<div class="card card-custom">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-receipt text-secondary me-1"></i> Transaksi Penjualan Terkini
        </h6>
        <a href="{{ route('admin.reports.sales') }}" class="small text-primary text-decoration-none">Lihat Seluruh Laporan</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>No. Faktur</th>
                        <th>Waktu Transaksi</th>
                        <th>Kasir</th>
                        <th class="text-center">Jumlah Barang</th>
                        <th class="text-end">Total Pembayaran</th>
                        <th>Metode Bayar</th>
                        <th class="text-end">Struk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.sales.detail', $tx) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $tx->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $tx->sold_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $tx->sold_at->format('H:i') }} WIB</small>
                            </td>
                            <td><span class="badge bg-light text-secondary border">{{ $tx->user->name ?? '-' }}</span></td>
                            <td class="text-center">{{ $tx->items->sum('quantity') }} unit</td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-dark text-uppercase">{{ $tx->payment_method }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.print', $tx) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi penjualan yang tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js 4.4 CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data dari Controller
        const labels = @json($chartDates);
        const salesData = @json($salesTrend);
        const profitData = @json($profitTrend);

        // 1. Chart Tren Penjualan & Laba
        const ctxSales = document.getElementById('salesTrendChart')?.getContext('2d');
        if (ctxSales) {
            new Chart(ctxSales, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Penjualan (Omzet)',
                            data: salesData,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: 'Laba Kotor',
                            data: profitData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + (value >= 1000 ? (value / 1000) + 'k' : value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Chart Top Produk Terlaris
        const topProds = @json($topProducts);
        const ctxTop = document.getElementById('topProductsChart')?.getContext('2d');
        if (ctxTop && topProds.length > 0) {
            new Chart(ctxTop, {
                type: 'doughnut',
                data: {
                    labels: topProds.map(p => p.product_name_snapshot),
                    datasets: [{
                        data: topProds.map(p => p.total_qty),
                        backgroundColor: [
                            '#3b82f6',
                            '#10b981',
                            '#f59e0b',
                            '#8b5cf6',
                            '#ec4899'
                        ],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12 } },
                    }
                }
            });
        }
    });
</script>
@endpush
