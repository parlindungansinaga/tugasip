@extends('layouts.app')

@section('title', 'Koreksi Stok Fisik - Gudang')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-1"><i class="bi bi-sliders text-info me-2"></i>Penyesuaian Stok Fisik</h4>
                <p class="text-muted small mb-0">Sesuaikan stok sistem dengan hasil opname fisik riil di gudang.</p>
            </div>
            <a href="{{ route('gudang.adjustments.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
            </a>
        </div>

        <form action="{{ route('gudang.adjustments.store') }}" method="POST" id="adjForm">
            @csrf

            <div class="card card-custom mb-4">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Tanggal & Waktu Penyesuaian <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="adjusted_at" class="form-control" 
                                   value="{{ old('adjusted_at', now()->format('Y-m-d\TH:i')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Catatan Umum / Referensi Dokumen Opname</label>
                            <input type="text" name="notes" class="form-control" 
                                   placeholder="Contoh: Stock Opname Bulanan Oktober 2026...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-custom mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-boxes me-1"></i> Daftar Barang yang Dikoreksi</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btnAddRow">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Baris
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 35%;">Pilih Barang</th>
                                    <th style="width: 15%;" class="text-center">Stok Sistem</th>
                                    <th style="width: 15%;" class="text-center">Stok Fisik Baru</th>
                                    <th style="width: 12%;" class="text-center">Selisih</th>
                                    <th style="width: 18%;">Alasan Koreksi</th>
                                    <th style="width: 5%;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="adjBody">
                                <!-- Dynamic rows inserted by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('gudang.adjustments.index') }}" class="btn btn-light px-4">Batal</a>
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-circle me-1"></i> Simpan Penyesuaian Stok
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const productList = @json($products);
    let rowIndex = 0;

    function addRow() {
        const tbody = document.getElementById('adjBody');
        const tr = document.createElement('tr');
        tr.id = `adj-row-${rowIndex}`;

        let options = '<option value="">-- Pilih Barang --</option>';
        productList.forEach(p => {
            options += `<option value="${p.id}" data-stock="${p.stock}" data-unit="${p.unit}">
                [${p.sku}] ${p.name}
            </option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required>
                    ${options}
                </select>
            </td>
            <td class="text-center">
                <span class="fw-bold current-stock text-muted">-</span>
            </td>
            <td class="text-center">
                <input type="number" min="0" name="items[${rowIndex}][stock_after]" 
                       class="form-control text-center new-stock-input" value="0" required>
            </td>
            <td class="text-center">
                <span class="badge diff-badge bg-secondary">0</span>
            </td>
            <td>
                <input type="text" name="items[${rowIndex}][reason]" class="form-control form-control-sm" 
                       placeholder="Misal: Rusak, Kadaluarsa, Selisih opname" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        const select = tr.querySelector('.product-select');
        const currentStockSpan = tr.querySelector('.current-stock');
        const newStockInput = tr.querySelector('.new-stock-input');
        const diffBadge = tr.querySelector('.diff-badge');

        function updateDiff() {
            const selected = select.options[select.selectedIndex];
            if (!selected.value) return;

            const current = parseInt(selected.getAttribute('data-stock')) || 0;
            const unit = selected.getAttribute('data-unit') || 'pcs';
            currentStockSpan.textContent = `${current} ${unit}`;

            const newStock = parseInt(newStockInput.value) || 0;
            const diff = newStock - current;

            if (diff > 0) {
                diffBadge.className = 'badge bg-success';
                diffBadge.textContent = `+${diff}`;
            } else if (diff < 0) {
                diffBadge.className = 'badge bg-danger';
                diffBadge.textContent = `${diff}`;
            } else {
                diffBadge.className = 'badge bg-secondary';
                diffBadge.textContent = '0 (Sama)';
            }
        }

        select.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (selected.value) {
                const current = parseInt(selected.getAttribute('data-stock')) || 0;
                newStockInput.value = current;
                updateDiff();
            }
        });

        newStockInput.addEventListener('input', updateDiff);

        tr.querySelector('.btn-remove').addEventListener('click', function () {
            if (tbody.querySelectorAll('tr').length > 1) {
                tr.remove();
            } else {
                alert('Minimal harus ada 1 barang dalam koreksi.');
            }
        });

        rowIndex++;
    }

    document.getElementById('btnAddRow').addEventListener('click', addRow);
    document.addEventListener('DOMContentLoaded', addRow);
</script>
@endpush
