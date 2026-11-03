<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Gudang\DashboardController as GudangDashboardController;
use App\Http\Controllers\Gudang\GoodsReceiptController;
use App\Http\Controllers\Gudang\ProductController;
use App\Http\Controllers\Gudang\StockAdjustmentController;
use App\Http\Controllers\Gudang\StockMovementController;
use App\Http\Controllers\Kasir\KasirController;
use App\Http\Controllers\Kasir\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Aplikasi Toko Retail Modern
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');

// Autentikasi Pengguna
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Modul Kasir (Role: kasir)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:kasir'])->prefix('kasir')->as('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('dashboard');
    Route::get('/products/search', [KasirController::class, 'searchProducts'])->name('products.search');
    Route::get('/products/barcode', [KasirController::class, 'findProductByBarcode'])->name('products.barcode');
    Route::post('/transaction', [TransactionController::class, 'store'])->name('transaction.store');
    Route::get('/receipt/{sale}', [TransactionController::class, 'printReceipt'])->name('print');
    Route::get('/history', [TransactionController::class, 'history'])->name('history');
});

/*
|--------------------------------------------------------------------------
| Modul Gudang (Role: gudang)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:gudang'])->prefix('gudang')->as('gudang.')->group(function () {
    Route::get('/', [GudangDashboardController::class, 'index'])->name('dashboard');

    // CRUD Master Produk
    Route::resource('products', ProductController::class);
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggleStatus'])->name('products.toggle');

    // Penerimaan Barang (Goods Receipt)
    Route::resource('receipts', GoodsReceiptController::class)->only(['index', 'create', 'store', 'show']);

    // Penyesuaian Stok (Stock Adjustment)
    Route::resource('adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

    // Riwayat Mutasi Stok
    Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
});

/*
|--------------------------------------------------------------------------
| Modul Admin (Role: admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Laporan-laporan
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/sales/{sale}', [ReportController::class, 'saleDetail'])->name('reports.sales.detail');
    Route::get('/reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('/receipt/{sale}', [TransactionController::class, 'printReceipt'])->name('print');
});
