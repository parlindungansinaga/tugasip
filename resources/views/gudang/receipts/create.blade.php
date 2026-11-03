@extends('layouts.app')

@section('title', 'Catat Barang Masuk - Gudang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-truck text-info me-2"></i>Penerimaan Barang Masuk</h4>
                <p class="text-muted small mb-0">Input pasokan barang dari supplier untuk menambah stok persediaan.</p>
            </div>
            <a href="{{ route('gudang.receipts.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>

        <form action="{{ route('gudang.receipts.store') }}" method="POST" id="receiptForm">
            @csrf

            <!-- Header Penerimaan -->
            <div class="card card-custom mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-file-earmark-text me-1"></i> Data Faktur Penerimaan</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Pemasok / Supplier</label>
                            <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                <option value="">-- Tanpa Supplier / Pembelian Langsung --</option>
                                @foreach($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>
                                        {{ $sup->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Tanggal & Waktu Terima <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="received_at" class="form-control @error('received_at') is-invalid @enderror" 
                                   value="{{ old('received_at', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Catatan / No. Surat Jalan</label>
                            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" 
                                   placeholder="Contoh: DO-1092 / PO-887...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Barang Masuk -->
            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-boxes me-1"></i> Daftar Barang Masuk</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddItem">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Baris Barang
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Pilih Produk Barang</th>
                                    <th style="width: 20%;" class="text-center">Jumlah Masuk</th>
                                    <th style="width: 25%;" class="text-end">Harga Beli Satuan (Rp)</th>
                                    <th style="width: 10%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Dynamic rows inserted by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('gudang.receipts.index') }}" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary-custom px-4" id="btnSubmit">
                    <i class="bi bi-check-circle me-1"></i> Konfirmasi Penerimaan & Tambah Stok
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Data produk untuk autofill
    const productList = @json($products);

    let rowIndex = 0;

    function createRow() {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.id = `row-${rowIndex}`;

        let options = '<option value="">-- Pilih Barang --</option>';
        productList.forEach(p => {
            options += `<option value="${p.id}" data-price="${p.purchase_price}" data-unit="${p.unit}">
                [${p.sku}] ${p.name} (Stok: ${p.stock} ${p.unit})
            </option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${options}
                </select>
            </td>
            <td class="text-center">
                <div class="input-group">
                    <input type="number" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input" 
                           min="1" value="1" required>
                    <span class="input-group-text unit-label text-muted small">pcs</span>
                </div>
            </td>
            <td class="text-end">
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted small">Rp</span>
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][purchase_price]" 
                           class="form-control text-end price-input" value="0" required>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove" title="Hapus Baris">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Bind change event to auto fill purchase price & unit
        const select = tr.querySelector('.product-select');
        const priceInput = tr.querySelector('.price-input');
        const unitLabel = tr.querySelector('.unit-label');

        select.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                priceInput.value = selected.getAttribute('data-price') || 0;
                unitLabel.textContent = selected.getAttribute('data-unit') || 'pcs';
            }
        });

        tr.querySelector('.btn-remove').addEventListener('click', function () {
            if (tbody.querySelectorAll('tr').length > 1) {
                tr.remove();
            } else {
                alert('Minimal harus ada 1 baris barang dalam penerimaan.');
            }
        });

        rowIndex++;
    }

    document.getElementById('btnAddItem').addEventListener('click', createRow);

    // Initial first row
    document.addEventListener('DOMContentLoaded', function () {
        createRow();
    });
</script>
@endpush
