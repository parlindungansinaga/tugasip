@extends('layouts.app')

@section('title', 'Kasir POS - Transaksi Penjualan')

@push('styles')
<style>
    .pos-container {
        height: calc(100vh - 120px);
    }
    .cart-scroll {
        max-height: calc(100vh - 350px);
        overflow-y: auto;
    }
    .quick-cash-btn {
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 8px;
    }
    .total-display-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        border-radius: 12px;
    }
    .search-results-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        max-height: 280px;
        overflow-y: auto;
    }
    .search-item-row:hover {
        background-color: #f1f5f9;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="row g-3">
    <!-- Kolom Kiri: Input Scan Barcode & Keranjang Belanja -->
    <div class="col-lg-8">
        <div class="card card-custom h-100 d-flex flex-column">
            <!-- Header Pencarian & Scanner -->
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-cart3 text-success me-2"></i>Kasir Penjualan POS
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light text-dark border">
                            <i class="bi bi-keyboard me-1"></i> <kbd>F4</kbd> Fokus Scan | <kbd>F2</kbd> Bayar
                        </span>
                    </div>
                </div>

                <div class="row g-2 position-relative">
                    <div class="col-10 col-md-11">
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary border-end-0">
                                <i class="bi bi-upc-scan fs-5"></i>
                            </span>
                            <input type="text" id="barcodeInput" class="form-control form-control-lg border-start-0 ps-0" 
                                   placeholder="Scan barcode USB atau ketik nama/SKU lalu Enter..." autofocus autocomplete="off">
                        </div>
                        <!-- Live Search Results Dropdown -->
                        <div id="searchResults" class="search-results-dropdown d-none"></div>
                    </div>
                    <div class="col-2 col-md-1 d-grid">
                        <button type="button" class="btn btn-outline-primary" id="btnOpenScanner" title="Buka Kamera Scan Barcode">
                            <i class="bi bi-camera-fill fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabel Keranjang Belanja -->
            <div class="card-body p-0 flex-grow-1 cart-scroll">
                <div class="table-responsive">
                    <table class="table table-custom mb-0">
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Nama Barang</th>
                                <th style="width: 18%;" class="text-end">Harga Satuan</th>
                                <th style="width: 15%;" class="text-center">Kuantitas</th>
                                <th style="width: 17%;" class="text-end">Subtotal</th>
                                <th style="width: 5%;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="cartTableBody">
                            <tr id="emptyCartRow">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-cart-x fs-1 text-secondary opacity-50 d-block mb-2"></i>
                                    Keranjang belanja masih kosong.<br>
                                    <small>Scan barcode atau cari nama produk untuk menambahkan ke keranjang.</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Keranjang: Ringkasan Item -->
            <div class="card-footer bg-light border-top py-2 px-3 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Total Item: <strong id="totalItemCount">0</strong> barang (<strong id="totalUnitCount">0</strong> unit)
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCart">
                    <i class="bi bi-trash me-1"></i> Kosongkan Keranjang
                </button>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Ringkasan Total & Panel Pembayaran -->
    <div class="col-lg-4">
        <div class="card card-custom h-100 p-3 d-flex flex-column justify-content-between">
            <div>
                <!-- Total Display Card -->
                <div class="total-display-card p-3 mb-3 shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-secondary text-uppercase fw-semibold">TOTAL TAGIHAN</small>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success">Lunas Kasir</span>
                    </div>
                    <h2 class="fw-bold mb-0 text-white" id="displayTotal">Rp 0</h2>
                </div>

                <!-- Metode Pembayaran -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Metode Pembayaran</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="payment_method" id="payTunai" value="tunai" checked>
                        <label class="btn btn-outline-secondary" for="payTunai"><i class="bi bi-cash me-1"></i> Tunai</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payQris" value="qris">
                        <label class="btn btn-outline-secondary" for="payQris"><i class="bi bi-qr-code me-1"></i> QRIS</label>

                        <input type="radio" class="btn-check" name="payment_method" id="payTransfer" value="transfer">
                        <label class="btn btn-outline-secondary" for="payTransfer"><i class="bi bi-credit-card me-1"></i> Transfer</label>
                    </div>
                </div>

                <!-- Input Nominal Bayar -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted">Nominal Pembayaran Diterima (Rp)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light fw-bold text-dark">Rp</span>
                        <input type="number" id="amountPaidInput" class="form-control form-control-lg fw-bold text-end" 
                               placeholder="0" min="0" step="500">
                    </div>
                </div>

                <!-- Tombol Cepat Uang Tunai (Quick Cash) -->
                <div class="mb-3" id="quickCashContainer">
                    <label class="form-label small fw-semibold text-muted mb-1">Pilihan Cepat:</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary quick-cash-btn flex-grow-1" id="btnExactCash">
                            Uang Pas
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-cash-btn flex-grow-1" data-val="10000">
                            10.000
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-cash-btn flex-grow-1" data-val="20000">
                            20.000
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-cash-btn flex-grow-1" data-val="50000">
                            50.000
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary quick-cash-btn flex-grow-1" data-val="100000">
                            100.000
                        </button>
                    </div>
                </div>

                <!-- Kembalian Display -->
                <div class="card bg-light border-0 p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small fw-semibold">UANG KEMBALIAN:</span>
                        <h4 class="fw-bold mb-0 text-success" id="displayChange">Rp 0</h4>
                    </div>
                    <small id="paymentWarning" class="text-danger small mt-1 d-none">
                        <i class="bi bi-exclamation-circle me-1"></i> Pembayaran masih kurang
                    </small>
                </div>
            </div>

            <!-- Tombol Proses Transaksi -->
            <div class="d-grid mt-2">
                <button type="button" class="btn btn-success btn-lg py-3 fw-bold shadow-sm" id="btnProcessPayment" disabled>
                    <i class="bi bi-check-circle-fill me-2"></i> PROSES PEMBAYARAN (<kbd>F2</kbd>)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Kamera Barcode Scanner (HTML5-QRCode) -->
<div class="modal fade" id="cameraScannerModal" tabindex="-1" aria-labelledby="cameraModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-header-title fw-bold mb-0" id="cameraModalLabel">
                    <i class="bi bi-camera me-1 text-primary"></i> Pindai Barcode via Kamera
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <div id="cameraPreview" style="width: 100%; min-height: 250px; background: #000; border-radius: 8px;"></div>
                <p class="small text-muted mt-2 mb-0">Arahkan kamera perangkat ke barcode produk barang.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup Kamera</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sukses Transaksi & Cetak Struk -->
<div class="modal fade" id="successTransactionModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-center p-4">
            <div class="mb-3 text-success">
                <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">Transaksi Berhasil!</h4>
            <p class="text-muted small mb-3">Faktur penjualan telah tersimpan dan stok barang telah terupdate otomatis.</p>

            <div class="bg-light p-3 rounded-3 mb-4 text-start">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">No. Faktur:</span>
                    <strong class="font-monospace text-primary" id="successInvoiceNo">-</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Total Tagihan:</span>
                    <strong id="successTotalAmount">-</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Uang Diterima:</span>
                    <strong id="successAmountPaid">-</strong>
                </div>
                <hr class="my-1">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Kembalian:</span>
                    <strong class="text-success fs-5" id="successChangeAmount">-</strong>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="#" target="_blank" class="btn btn-primary btn-lg fw-semibold" id="btnReceiptPrint">
                    <i class="bi bi-printer-fill me-2"></i> Cetak Struk Penjualan
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btnNewTransaction">
                    <i class="bi bi-plus-lg me-1"></i> Transaksi Baru (Esc)
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Library HTML5 QR Code Scanner -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // State Keranjang Belanja
        let cart = [];
        let isSubmitting = false;

        const barcodeInput = document.getElementById('barcodeInput');
        const searchResults = document.getElementById('searchResults');
        const cartTableBody = document.getElementById('cartTableBody');
        const emptyCartRow = document.getElementById('emptyCartRow');
        const displayTotal = document.getElementById('displayTotal');
        const displayChange = document.getElementById('displayChange');
        const amountPaidInput = document.getElementById('amountPaidInput');
        const paymentWarning = document.getElementById('paymentWarning');
        const btnProcessPayment = document.getElementById('btnProcessPayment');
        const totalItemCount = document.getElementById('totalItemCount');
        const totalUnitCount = document.getElementById('totalUnitCount');

        // Helpers format rupiah
        function formatRp(val) {
            return 'Rp ' + Math.round(val).toLocaleString('id-ID');
        }

        function calculateCartTotal() {
            return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        function renderCart() {
            if (cart.length === 0) {
                cartTableBody.innerHTML = '';
                cartTableBody.appendChild(emptyCartRow);
                displayTotal.textContent = 'Rp 0';
                displayChange.textContent = 'Rp 0';
                totalItemCount.textContent = '0';
                totalUnitCount.textContent = '0';
                btnProcessPayment.disabled = true;
                return;
            }

            cartTableBody.innerHTML = '';
            let totalUnits = 0;

            cart.forEach((item, index) => {
                totalUnits += item.quantity;
                const subtotal = item.price * item.quantity;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>
                        <div class="fw-semibold text-dark">${item.name}</div>
                        <small class="text-muted font-monospace">${item.sku} ${item.barcode ? '| ' + item.barcode : ''}</small>
                    </td>
                    <td class="text-end">${formatRp(item.price)}</td>
                    <td class="text-center">
                        <div class="input-group input-group-sm justify-content-center" style="max-width: 120px; margin: 0 auto;">
                            <button class="btn btn-outline-secondary btn-qty-dec" data-index="${index}" type="button">-</button>
                            <input type="text" class="form-control text-center px-1" value="${item.quantity}" readonly>
                            <button class="btn btn-outline-secondary btn-qty-inc" data-index="${index}" type="button">+</button>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">Sisa: ${item.stock} ${item.unit}</small>
                    </td>
                    <td class="text-end fw-bold text-dark">${formatRp(subtotal)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-item" data-index="${index}" title="Hapus">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </button>
                    </td>
                `;
                cartTableBody.appendChild(tr);
            });

            const grandTotal = calculateCartTotal();
            displayTotal.textContent = formatRp(grandTotal);
            totalItemCount.textContent = cart.length;
            totalUnitCount.textContent = totalUnits;

            btnProcessPayment.disabled = false;
            updateChangeCalculation();
        }

        function addToCart(product) {
            const existing = cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.quantity + 1 > product.stock) {
                    alert(`Stok barang '${product.name}' tidak mencukupi (Tersedia: ${product.stock} ${product.unit}).`);
                    return;
                }
                existing.quantity += 1;
            } else {
                if (product.stock <= 0) {
                    alert(`Stok barang '${product.name}' habis.`);
                    return;
                }
                cart.push({
                    id: product.id,
                    sku: product.sku,
                    barcode: product.barcode,
                    name: product.name,
                    price: parseFloat(product.selling_price),
                    stock: parseInt(product.stock),
                    unit: product.unit,
                    quantity: 1
                });
            }
            renderCart();
            barcodeInput.value = '';
            searchResults.classList.add('d-none');
            barcodeInput.focus();
        }

        // Event listener qty modifier & delete
        cartTableBody.addEventListener('click', function (e) {
            const btnInc = e.target.closest('.btn-qty-inc');
            const btnDec = e.target.closest('.btn-qty-dec');
            const btnRemove = e.target.closest('.btn-remove-item');

            if (btnInc) {
                const idx = parseInt(btnInc.getAttribute('data-index'));
                if (cart[idx].quantity + 1 > cart[idx].stock) {
                    alert(`Stok maksimum '${cart[idx].name}' adalah ${cart[idx].stock} ${cart[idx].unit}.`);
                    return;
                }
                cart[idx].quantity += 1;
                renderCart();
            } else if (btnDec) {
                const idx = parseInt(btnDec.getAttribute('data-index'));
                if (cart[idx].quantity > 1) {
                    cart[idx].quantity -= 1;
                } else {
                    cart.splice(idx, 1);
                }
                renderCart();
            } else if (btnRemove) {
                const idx = parseInt(btnRemove.getAttribute('data-index'));
                cart.splice(idx, 1);
                renderCart();
            }
        });

        // Kosongkan Keranjang
        document.getElementById('btnClearCart').addEventListener('click', function () {
            if (cart.length > 0 && confirm('Kosongkan semua barang dalam keranjang belanja?')) {
                cart = [];
                renderCart();
            }
        });

        // Search Autocomplete / Barcode scan
        let searchTimeout = null;
        barcodeInput.addEventListener('input', function () {
            const val = this.value.trim();
            clearTimeout(searchTimeout);

            if (val.length < 2) {
                searchResults.classList.add('d-none');
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('kasir.products.search') }}?q=${encodeURIComponent(val)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.length === 0) {
                            searchResults.innerHTML = `<div class="p-3 text-muted small text-center">Barang tidak ditemukan.</div>`;
                            searchResults.classList.remove('d-none');
                            return;
                        }

                        let html = '';
                        data.forEach(p => {
                            html += `
                                <div class="p-2 border-bottom search-item-row d-flex justify-content-between align-items-center" 
                                     data-product='${JSON.stringify(p)}'>
                                    <div>
                                        <div class="fw-semibold text-dark">${p.name}</div>
                                        <small class="text-muted font-monospace">${p.sku} ${p.barcode ? '| ' + p.barcode : ''}</small>
                                    </div>
                                    <div class="text-end">
                                        <strong class="text-primary">${formatRp(p.selling_price)}</strong><br>
                                        <small class="badge ${p.stock <= 0 ? 'bg-danger' : 'bg-light text-secondary border'}">Stok: ${p.stock} ${p.unit}</small>
                                    </div>
                                </div>
                            `;
                        });
                        searchResults.innerHTML = html;
                        searchResults.classList.remove('d-none');
                    });
            }, 250);
        });

        // Click search result item
        searchResults.addEventListener('click', function (e) {
            const row = e.target.closest('.search-item-row');
            if (row) {
                const product = JSON.parse(row.getAttribute('data-product'));
                addToCart(product);
            }
        });

        // Barcode USB Scanner Enter Key Press
        barcodeInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = this.value.trim();
                if (!code) return;

                fetch(`{{ route('kasir.products.barcode') }}?code=${encodeURIComponent(code)}`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.found) {
                            addToCart(res.product);
                        } else {
                            alert(res.message || 'Barang tidak ditemukan.');
                            barcodeInput.select();
                        }
                    })
                    .catch(() => {
                        alert('Terjadi kesalahan koneksi scanner.');
                    });
            }
        });

        // Close search dropdown on click outside
        document.addEventListener('click', function (e) {
            if (!barcodeInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('d-none');
            }
        });

        // Live Change Calculation
        function updateChangeCalculation() {
            const grandTotal = calculateCartTotal();
            const paid = parseFloat(amountPaidInput.value) || 0;

            if (paid >= grandTotal) {
                const change = paid - grandTotal;
                displayChange.textContent = formatRp(change);
                displayChange.className = 'fw-bold mb-0 text-success';
                paymentWarning.classList.add('d-none');
                btnProcessPayment.disabled = (cart.length === 0);
            } else {
                displayChange.textContent = 'Rp 0';
                displayChange.className = 'fw-bold mb-0 text-secondary';
                if (paid > 0) {
                    paymentWarning.classList.remove('d-none');
                } else {
                    paymentWarning.classList.add('d-none');
                }
                btnProcessPayment.disabled = true;
            }
        }

        amountPaidInput.addEventListener('input', updateChangeCalculation);

        // Quick Cash Buttons
        document.getElementById('btnExactCash').addEventListener('click', function () {
            amountPaidInput.value = calculateCartTotal();
            updateChangeCalculation();
        });

        document.querySelectorAll('.quick-cash-btn[data-val]').forEach(btn => {
            btn.addEventListener('click', function () {
                const addVal = parseFloat(this.getAttribute('data-val'));
                amountPaidInput.value = addVal;
                updateChangeCalculation();
            });
        });

        // Payment method toggle (QRIS / Transfer autofill exact amount)
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'qris' || this.value === 'transfer') {
                    amountPaidInput.value = calculateCartTotal();
                    updateChangeCalculation();
                }
            });
        });

        // Commit Payment Process (AJAX)
        btnProcessPayment.addEventListener('click', function () {
            if (isSubmitting) return;

            const total = calculateCartTotal();
            const paid = parseFloat(amountPaidInput.value) || 0;

            if (cart.length === 0) {
                alert('Keranjang belanja kosong.');
                return;
            }

            if (paid < total) {
                alert('Uang pembayaran masih kurang dari total transaksi.');
                amountPaidInput.focus();
                return;
            }

            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

            const payload = {
                items: cart.map(i => ({ product_id: i.id, quantity: i.quantity })),
                amount_paid: paid,
                payment_method: paymentMethod,
                discount_amount: 0,
            };

            isSubmitting = true;
            btnProcessPayment.disabled = true;
            btnProcessPayment.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span> Memproses...`;

            fetch(`{{ route('kasir.transaction.store') }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                isSubmitting = false;
                btnProcessPayment.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> PROSES PEMBAYARAN (<kbd>F2</kbd>)`;

                if (data.success) {
                    // Tampilkan Modal Sukses & Tombol Cetak Struk
                    document.getElementById('successInvoiceNo').textContent = data.invoice_number;
                    document.getElementById('successTotalAmount').textContent = formatRp(data.total_amount);
                    document.getElementById('successAmountPaid').textContent = formatRp(data.amount_paid);
                    document.getElementById('successChangeAmount').textContent = formatRp(data.change_amount);
                    document.getElementById('btnReceiptPrint').href = data.receipt_url;

                    const successModal = new bootstrap.Modal(document.getElementById('successTransactionModal'));
                    successModal.show();

                    // Reset keranjang
                    cart = [];
                    amountPaidInput.value = '';
                    renderCart();
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan sistem.'));
                    btnProcessPayment.disabled = false;
                }
            })
            .catch(err => {
                isSubmitting = false;
                btnProcessPayment.disabled = false;
                btnProcessPayment.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> PROSES PEMBAYARAN (<kbd>F2</kbd>)`;
                alert('Terjadi kesalahan jaringan atau server.');
            });
        });

        // Hotkeys Keyboard (F2, F4, Esc)
        document.addEventListener('keydown', function (e) {
            if (e.key === 'F4') {
                e.preventDefault();
                barcodeInput.focus();
                barcodeInput.select();
            } else if (e.key === 'F2') {
                e.preventDefault();
                if (!btnProcessPayment.disabled) {
                    btnProcessPayment.click();
                } else {
                    amountPaidInput.focus();
                    amountPaidInput.select();
                }
            }
        });

        // Reset focus on transaction complete
        document.getElementById('btnNewTransaction').addEventListener('click', function () {
            barcodeInput.focus();
        });

        // HTML5 Camera QR/Barcode Scanner
        let html5QrCode = null;
        const cameraModalEl = document.getElementById('cameraScannerModal');
        const cameraModal = new bootstrap.Modal(cameraModalEl);

        document.getElementById('btnOpenScanner').addEventListener('click', function () {
            cameraModal.show();
        });

        cameraModalEl.addEventListener('shown.bs.modal', function () {
            html5QrCode = new Html5Qrcode("cameraPreview");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 150 } },
                (decodedText) => {
                    // Scanned barcode successfully
                    html5QrCode.stop().then(() => {
                        cameraModal.hide();
                        barcodeInput.value = decodedText;
                        // Trigger fetch
                        fetch(`{{ route('kasir.products.barcode') }}?code=${encodeURIComponent(decodedText)}`)
                            .then(res => res.json())
                            .then(res => {
                                if (res.found) {
                                    addToCart(res.product);
                                } else {
                                    alert(res.message || 'Barang tidak ditemukan.');
                                }
                            });
                    });
                },
                (errorMessage) => {
                    // parse error ignored
                }
            ).catch(err => {
                alert('Tidak dapat mengakses kamera: ' + err);
                cameraModal.hide();
            });
        });

        cameraModalEl.addEventListener('hidden.bs.modal', function () {
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop();
            }
        });
    });
</script>
@endpush
