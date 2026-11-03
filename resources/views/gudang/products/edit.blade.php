@extends('layouts.app')

@section('title', 'Edit Data Barang - Gudang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-pencil-square text-warning me-2"></i>Edit Data Barang</h4>
                <p class="text-muted small mb-0">Perbarui informasi barang: <strong>{{ $product->name }}</strong></p>
            </div>
            <a href="{{ route('gudang.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
        </div>

        <div class="card card-custom">
            <div class="card-body p-4">
                <form action="{{ route('gudang.products.update', $product) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-1"></i> Informasi Dasar Produk</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kode SKU / ID Barang <span class="text-danger">*</span></label>
                            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" 
                                   value="{{ old('sku', $product->sku) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Barcode / QR Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" 
                                       value="{{ old('barcode', $product->barcode) }}">
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Produk Barang <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $product->name) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" 
                                   value="{{ old('unit', $product->unit) }}" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-currency-dollar me-1"></i> Harga & Persediaan Stok</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Harga Beli Pokok (HPP) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" min="0" name="purchase_price" 
                                       class="form-control @error('purchase_price') is-invalid @enderror" 
                                       value="{{ old('purchase_price', $product->purchase_price) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Harga Jual Kasir <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" min="0" name="selling_price" 
                                       class="form-control @error('selling_price') is-invalid @enderror" 
                                       value="{{ old('selling_price', $product->selling_price) }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Stok Saat Ini (Hanya Baca)</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" value="{{ $product->stock }} {{ $product->unit }}" readonly>
                                <a href="{{ route('gudang.adjustments.create') }}" class="btn btn-outline-info" title="Lakukan Penyesuaian Stok">
                                    <i class="bi bi-sliders me-1"></i> Sesuaikan
                                </a>
                            </div>
                            <small class="text-muted">Untuk menjaga integritas audit, perubahan stok wajib melalui fitur Penyesuaian atau Barang Masuk.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batas Minimum Stok (Peringatan) <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="minimum_stock" 
                                   class="form-control @error('minimum_stock') is-invalid @enderror" 
                                   value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
                        </div>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">Aktifkan Barang untuk Dijual di Kasir</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('gudang.products.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="bi bi-save me-1"></i> Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
