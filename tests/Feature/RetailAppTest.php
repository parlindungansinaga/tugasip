<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RetailAppTest extends TestCase
{
    protected User $adminUser;
    protected User $gudangUser;
    protected User $kasirUser;
    protected User $inactiveUser;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun demo pengujian
        $this->adminUser = User::firstOrCreate(
            ['username' => 'test_admin'],
            ['name' => 'Admin Test', 'email' => 'adm_test@toko.com', 'password' => Hash::make('secret123'), 'role' => 'admin', 'is_active' => true]
        );

        $this->gudangUser = User::firstOrCreate(
            ['username' => 'test_gudang'],
            ['name' => 'Gudang Test', 'email' => 'gdg_test@toko.com', 'password' => Hash::make('secret123'), 'role' => 'gudang', 'is_active' => true]
        );

        $this->kasirUser = User::firstOrCreate(
            ['username' => 'test_kasir'],
            ['name' => 'Kasir Test', 'email' => 'ksr_test@toko.com', 'password' => Hash::make('secret123'), 'role' => 'kasir', 'is_active' => true]
        );

        $this->inactiveUser = User::firstOrCreate(
            ['username' => 'test_nonaktif'],
            ['name' => 'Nonaktif Test', 'email' => 'off_test@toko.com', 'password' => Hash::make('secret123'), 'role' => 'kasir', 'is_active' => false]
        );

        $category = Category::firstOrCreate(['name' => 'Sembako Test']);

        $this->product = Product::firstOrCreate(
            ['sku' => 'SKU-TEST-MIE'],
            [
                'barcode' => '8999999000111',
                'name' => 'Mie Instan Uji Rasa 80g',
                'category_id' => $category->id,
                'unit' => 'pcs',
                'purchase_price' => 2500,
                'selling_price' => 3500,
                'stock' => 50,
                'minimum_stock' => 10,
                'is_active' => true,
            ]
        );
    }

    // ==========================================
    // 1. PENGUJIAN LOGIN & ROLE REDIRECT
    // ==========================================

    public function test_kasir_login_redirects_to_kasir_dashboard()
    {
        $response = $this->post(route('login.post'), [
            'login' => 'test_kasir',
            'password' => 'secret123',
        ]);
        $response->assertRedirect(route('kasir.dashboard'));
    }

    public function test_admin_login_redirects_to_admin_dashboard()
    {
        $response = $this->post(route('login.post'), [
            'login' => 'test_admin',
            'password' => 'secret123',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_gudang_login_redirects_to_gudang_dashboard()
    {
        $response = $this->post(route('login.post'), [
            'login' => 'test_gudang',
            'password' => 'secret123',
        ]);
        $response->assertRedirect(route('gudang.dashboard'));
    }

    public function test_login_with_wrong_password_fails()
    {
        $response = $this->post(route('login.post'), [
            'login' => 'test_kasir',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_login_with_inactive_account_is_rejected()
    {
        $response = $this->post(route('login.post'), [
            'login' => 'test_nonaktif',
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    // ==========================================
    // 2. PENGUJIAN KEAMANAN & PEMBATASAN ROLE
    // ==========================================

    public function test_kasir_cannot_access_gudang_or_admin()
    {
        $this->actingAs($this->kasirUser);

        // Akses Gudang ditolak (403)
        $resGudang = $this->get(route('gudang.dashboard'));
        $resGudang->assertStatus(403);

        // Akses Admin ditolak (403)
        $resAdmin = $this->get(route('admin.dashboard'));
        $resAdmin->assertStatus(403);
    }

    public function test_gudang_cannot_access_kasir_or_admin()
    {
        $this->actingAs($this->gudangUser);

        // Akses Kasir ditolak (403)
        $resKasir = $this->get(route('kasir.dashboard'));
        $resKasir->assertStatus(403);

        // Akses Admin ditolak (403)
        $resAdmin = $this->get(route('admin.dashboard'));
        $resAdmin->assertStatus(403);
    }

    public function test_admin_cannot_access_kasir_or_gudang()
    {
        $this->actingAs($this->adminUser);

        // Akses Kasir ditolak (403)
        $resKasir = $this->get(route('kasir.dashboard'));
        $resKasir->assertStatus(403);

        // Akses Gudang ditolak (403)
        $resGudang = $this->get(route('gudang.dashboard'));
        $resGudang->assertStatus(403);
    }

    // ==========================================
    // 3. PENGUJIAN MODUL KASIR & TRANSAKSI
    // ==========================================

    public function test_kasir_can_search_and_scan_barcode()
    {
        $this->actingAs($this->kasirUser);

        // Cari via Keyword
        $resSearch = $this->getJson(route('kasir.products.search', ['q' => 'Mie Instan']));
        $resSearch->assertStatus(200)
            ->assertJsonFragment(['name' => 'Mie Instan Uji Rasa 80g']);

        // Cari via Barcode persis
        $resBarcode = $this->getJson(route('kasir.products.barcode', ['code' => '8999999000111']));
        $resBarcode->assertStatus(200)
            ->assertJson(['found' => true])
            ->assertJsonPath('product.sku', 'SKU-TEST-MIE');
    }

    public function test_kasir_sales_transaction_reduces_stock_and_records_movement()
    {
        $this->actingAs($this->kasirUser);

        $initialStock = $this->product->fresh()->stock;
        $qtyToBuy = 5;
        $expectedTotal = $this->product->selling_price * $qtyToBuy;
        $amountPaid = 20000;

        $response = $this->postJson(route('kasir.transaction.store'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => $qtyToBuy]
            ],
            'amount_paid' => $amountPaid,
            'payment_method' => 'tunai',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertEquals($expectedTotal, $response->json('total_amount'));
        $this->assertEquals($amountPaid - $expectedTotal, $response->json('change_amount'));

        // Pastikan stok berkurang
        $newStock = $this->product->fresh()->stock;
        $this->assertEquals($initialStock - $qtyToBuy, $newStock);

        // Pastikan tercatat di mutasi stok
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'movement_type' => 'keluar',
            'quantity_out' => $qtyToBuy,
            'stock_after' => $newStock,
        ]);
    }

    public function test_sales_transaction_rejected_when_exceeds_stock()
    {
        $this->actingAs($this->kasirUser);

        $currentStock = $this->product->fresh()->stock;
        $excessiveQty = $currentStock + 10;

        $response = $this->postJson(route('kasir.transaction.store'), [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => $excessiveQty]
            ],
            'amount_paid' => 1000000,
            'payment_method' => 'tunai',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);

        // Stok tidak boleh berubah
        $this->assertEquals($currentStock, $this->product->fresh()->stock);
    }

    // ==========================================
    // 4. PENGUJIAN MODUL GUDANG
    // ==========================================

    public function test_gudang_can_record_goods_receipt_and_increases_stock()
    {
        $this->actingAs($this->gudangUser);

        $supplier = Supplier::firstOrCreate(['name' => 'Supplier Uji Coba']);
        $initialStock = $this->product->fresh()->stock;
        $qtyIn = 20;

        $response = $this->post(route('gudang.receipts.store'), [
            'supplier_id' => $supplier->id,
            'received_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Penerimaan uji coba sistem',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => $qtyIn,
                    'purchase_price' => 2600,
                ]
            ]
        ]);

        $response->assertSessionHas('success');

        // Pastikan stok bertambah
        $this->assertEquals($initialStock + $qtyIn, $this->product->fresh()->stock);
    }

    public function test_gudang_can_record_stock_adjustment()
    {
        $this->actingAs($this->gudangUser);

        $newPhysicalStock = 100;

        $response = $this->post(route('gudang.adjustments.store'), [
            'adjusted_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Opname stok uji coba',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'stock_after' => $newPhysicalStock,
                    'reason' => 'Koreksi hitung fisik',
                ]
            ]
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals($newPhysicalStock, $this->product->fresh()->stock);
    }

    // ==========================================
    // 5. PENGUJIAN MODUL ADMIN & LAPORAN
    // ==========================================

    public function test_admin_can_view_reports_and_export_csv()
    {
        $this->actingAs($this->adminUser);

        // Dashboard
        $this->get(route('admin.dashboard'))->assertStatus(200);

        // Laporan Penjualan
        $this->get(route('admin.reports.sales'))->assertStatus(200);

        // Laporan Keuntungan Kotor
        $this->get(route('admin.reports.profit'))->assertStatus(200);

        // Laporan Stok
        $this->get(route('admin.reports.stock'))->assertStatus(200);

        // Ekspor CSV
        $resCsv = $this->get(route('admin.reports.sales', ['export' => 'csv']));
        $resCsv->assertStatus(200)
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    }
}
