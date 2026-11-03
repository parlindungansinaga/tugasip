@extends('layouts.app')

@section('title', 'Penyesuaian Stok Fisik - Gudang')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-sliders text-info me-2"></i>Penyesuaian & Opname Stok</h4>
        <p class="text-muted small mb-0">Koreksi stok fisik jika terjadi selisih, barang rusak, atau kadaluarsa.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('gudang.adjustments.create') }}" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-1"></i> Buat Koreksi Stok Baru
        </a>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Nomor Koreksi</th>
                        <th>Tanggal Koreksi</th>
                        <th>Petugas Gudang</th>
                        <th class="text-center">Jumlah Barang</th>
                        <th>Catatan Alasan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        <tr>
                            <td>
                                <a href="{{ route('gudang.adjustments.show', $adj) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                    {{ $adj->adjustment_number }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $adj->adjusted_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $adj->adjusted_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $adj->user->name ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">
                                    {{ $adj->items->count() }} produk disesuaikan
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($adj->notes, 50) ?: 'Penyesuaian stok opname' }}</small>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('gudang.adjustments.show', $adj) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye me-1"></i> Rincian
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                Belum ada riwayat koreksi stok fisik.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($adjustments->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $adjustments->links() }}
        </div>
    @endif
</div>
@endsection
