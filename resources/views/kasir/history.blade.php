@extends('layouts.app')

@section('title', 'Riwayat Transaksi Saya - Kasir')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-receipt text-success me-2"></i>Riwayat Transaksi Penjualan Saya</h4>
        <p class="text-muted small mb-0">Daftar transaksi yang diproses oleh kasir aktif saat ini.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('kasir.dashboard') }}" class="btn btn-primary-custom">
            <i class="bi bi-cart-plus me-1"></i> Kembali ke Kasir POS
        </a>
    </div>
</div>

<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('kasir.history') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <input type="text" name="invoice_number" class="form-control form-control-sm" 
                       placeholder="Cari nomor faktur (INV-...)" value="{{ request('invoice_number') }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
            </div>
            <div class="col-6 col-md-3">
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('kasir.history') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
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
                        <th>No. Faktur</th>
                        <th>Waktu Transaksi</th>
                        <th class="text-center">Jumlah Barang</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Uang Bayar</th>
                        <th class="text-end">Kembalian</th>
                        <th>Metode</th>
                        <th class="text-end">Struk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-primary">{{ $s->invoice_number }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $s->sold_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $s->sold_at->format('H:i') }} WIB</small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-secondary border">
                                    {{ $s->items->sum('quantity') }} unit
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($s->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($s->amount_paid, 0, ',', '.') }}
                            </td>
                            <td class="text-end text-success fw-semibold">
                                Rp {{ number_format($s->change_amount, 0, ',', '.') }}
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-dark text-uppercase">{{ $s->payment_method }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('kasir.print', $s) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-printer me-1"></i> Cetak Ulang
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Belum ada riwayat transaksi yang Anda lakukan.
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
