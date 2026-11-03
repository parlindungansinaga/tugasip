@extends('layouts.app')

@section('title', 'Katalog Data Barang - Gudang')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold mb-1"><i class="bi bi-boxes text-info me-2"></i>Katalog Data Barang</h4>
        <p class="text-muted small mb-0">Kelola master produk, SKU, barcode, harga jual, dan batas stok.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('gudang.products.create') }}" class="btn btn-primary-custom">
            <i class="bi bi-plus-circle me-1"></i> Tambah Barang Baru
        </a>
    </div>
</div>

<!-- Filter & Pencarian -->
<div class="card card-custom mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('gudang.products.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" 
                           placeholder="Cari nama, SKU, atau barcode..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">Semua Status Stok</option>
                    <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis (<= Min)</option>
                    <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">Filter</button>
                <a href="{{ route('gudang.products.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Barang -->
<div class="card card-custom">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead>
                    <tr>
                        <th>Kode / SKU</th>
                        <th>Barcode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Sisa Stok</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="fw-bold font-monospace text-dark">{{ $product->sku }}</span>
                            </td>
                            <td>
                                @if($product->barcode)
                                    <span class="badge bg-light text-dark border font-monospace">
                                        <i class="bi bi-upc-scan me-1"></i>{{ $product->barcode }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('gudang.products.show', $product) }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ $product->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border">{{ $product->category->name ?? '-' }}</span>
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold text-dark">
                                Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($product->stock <= 0)
                                    <span class="badge bg-danger">0 {{ $product->unit }}</span>
                                @elseif($product->isLowStock())
                                    <span class="badge bg-warning text-dark">{{ $product->stock }} {{ $product->unit }}</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ $product->stock }} {{ $product->unit }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($product->is_active)
                                    <span class="badge bg-success rounded-pill px-2">Aktif</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-2">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Opsi
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('gudang.products.show', $product) }}">
                                                <i class="bi bi-eye text-primary me-2"></i> Detail & Mutasi
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('gudang.products.edit', $product) }}">
                                                <i class="bi bi-pencil-square text-warning me-2"></i> Edit Data
                                            </a>
                                        </li>
                                        <li>
                                            <form action="{{ route('gudang.products.toggle', $product) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item">
                                                    @if($product->is_active)
                                                        <i class="bi bi-pause-circle text-secondary me-2"></i> Nonaktifkan
                                                    @else
                                                        <i class="bi bi-play-circle text-success me-2"></i> Aktifkan
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('gudang.products.destroy', $product) }}" method="POST" 
                                                  onsubmit="return confirm('Yakin ingin menghapus produk ini? Produk yang memiliki riwayat transaksi tidak dapat dihapus permanen.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Hapus
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                Tidak ada data barang yang sesuai dengan filter.
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
