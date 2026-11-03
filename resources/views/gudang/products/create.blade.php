@extends('layouts.app')

@section('title', 'Tambah Barang Baru - Gudang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-plus-circle text-info me-2"></i>Tambah Barang Baru</h4>
                <p class="text-muted small mb-0">Isi informasi barang untuk didaftarkan ke dalam sistem persediaan.</p>
            </div>
            <a href="{{ route('gudang.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
            </a>
        </div>

        <div class="card card-custom">
            <div class="card-body p-4">
                <form action="{{ route('gudang.products.store') }}" method="POST">
                    @csrf

                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-1"></i> Informasi Dasar Produk</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kode SKU / ID Barang <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="sku" id="skuInput" class="form-control @error('sku') is-invalid @enderror" 
                                       value="{{ old('sku') }}" placeholder="Contoh: SKU-INDOMIE-01" required>
                                <button class="btn btn-outline-secondary" type="button" id="btnGenSku">
                                    <i class="bi bi-magic me-1"></i> Auto
                                </button>
                            </div>
                            <small class="text-muted">Kode unik identifikasi sistem gudang.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Barcode / QR Code</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" 
                                       value="{{ old('barcode') }}" placeholder="Scan atau ketik nomor barcode EAN-13...">
                            </div>
                            <small class="text-muted">Dapat dipindai langsung di kasir melalui scanner.</small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Nama Produk Barang <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" placeholder="Contoh: Indomie Goreng Spesial 85g" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Satuan Barang <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" 
                                   value="{{ old('unit', 'pcs') }}" placeholder="pcs / botol / pack / kg" required>
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
                                       value="{{ old('purchase_price', 0) }}" required>
                            </div>
                            <small class="text-muted">Biaya modal per unit untuk perhitungan laba.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Harga Jual Kasir <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" step="0.01" min="0" name="selling_price" 
                                       class="form-control @error('selling_price') is-invalid @enderror" 
                                       value="{{ old('selling_price', 0) }}" required>
                            </div>
                            <small class="text-muted">Harga resmi yang dibebankan kepada pembeli.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Stok Awal Fisik <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="initial_stock" 
                                   class="form-control @error('initial_stock') is-invalid @enderror" 
                                   value="{{ old('initial_stock', 0) }}" required>
                            <small class="text-muted">Akan otomatis dicatat ke riwayat mutasi stok.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batas Minimum Stok (Peringatan) <span class="text-danger">*</span></label>
                            <input type="number" min="0" name="minimum_stock" 
                                   class="form-control @error('minimum_stock') is-invalid @enderror" 
                                   value="{{ old('minimum_stock', 5) }}" required>
                            <small class="text-muted">Sistem memberi tanda peringatan jika sisa stok kurang dari ini.</small>
                        </div>
                    </div>

                    <div class="mb-4 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="is_active">Aktifkan Barang untuk Dijual di Kasir</label>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('gudang.products.index') }}" class="btn btn-light px-4">Batal</a>
                        <button type="submit" class="btn btn-primary-custom px-4">
                            <i class="bi bi-save me-1"></i> Simpan Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('btnGenSku')?.addEventListener('click', function () {
        const rand = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('skuInput').value = 'PRD-' + Date.now().toString().slice(-4) + '-' + rand;
    });
</script>
@endpush
