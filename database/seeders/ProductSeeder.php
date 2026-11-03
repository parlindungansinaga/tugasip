<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catFood = Category::where('name', 'Makanan & Minuman')->first();
        $catSembako = Category::where('name', 'Sembako')->first();
        $catCare = Category::where('name', 'Perawatan Tubuh')->first();
        $catHome = Category::where('name', 'Kebutuhan Rumah Tangga')->first();
        $catAtk = Category::where('name', 'Alat Tulis Kantor')->first();

        $adminUser = User::where('role', 'admin')->first();

        $products = [
            [
                'sku' => 'SKU-INDOMIE-GOR',
                'barcode' => '8998866200225',
                'name' => 'Indomie Goreng Spesial 85g',
                'category_id' => $catFood->id,
                'unit' => 'pcs',
                'purchase_price' => 2800,
                'selling_price' => 3500,
                'stock' => 120,
                'minimum_stock' => 20,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-LEMINERALE-600',
                'barcode' => '8996001600269',
                'name' => 'Le Minerale 600ml',
                'category_id' => $catFood->id,
                'unit' => 'botol',
                'purchase_price' => 2500,
                'selling_price' => 4000,
                'stock' => 85,
                'minimum_stock' => 15,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-MINYAK-BIMOLI-2L',
                'barcode' => '8991002105432',
                'name' => 'Minyak Goreng Bimoli Pouch 2L',
                'category_id' => $catSembako->id,
                'unit' => 'pouch',
                'purchase_price' => 32000,
                'selling_price' => 37000,
                'stock' => 40,
                'minimum_stock' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-GULA-GULAKU-1KG',
                'barcode' => '8993417101019',
                'name' => 'Gulaku Kuning Premium 1kg',
                'category_id' => $catSembako->id,
                'unit' => 'pack',
                'purchase_price' => 14500,
                'selling_price' => 17500,
                'stock' => 50,
                'minimum_stock' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-PEPSODENT-190',
                'barcode' => '8999999042562',
                'name' => 'Pasta Gigi Pepsodent Pencegah Gigi Berlubang 190g',
                'category_id' => $catCare->id,
                'unit' => 'tube',
                'purchase_price' => 12000,
                'selling_price' => 16000,
                'stock' => 4, // Low stock demo!
                'minimum_stock' => 10,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-LIFEBUOY-RED',
                'barcode' => '8999999021116',
                'name' => 'Sabun Mandi Batang Lifebuoy Merah 85g',
                'category_id' => $catCare->id,
                'unit' => 'pcs',
                'purchase_price' => 3500,
                'selling_price' => 5000,
                'stock' => 60,
                'minimum_stock' => 15,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-RINSO-ANTI-NODA-770',
                'barcode' => '8999999054321',
                'name' => 'Detergen Bubuk Rinso Anti Noda 770g',
                'category_id' => $catHome->id,
                'unit' => 'pack',
                'purchase_price' => 18000,
                'selling_price' => 23000,
                'stock' => 0, // Out of stock demo!
                'minimum_stock' => 5,
                'is_active' => true,
            ],
            [
                'sku' => 'SKU-PULPEN-STANDARD-AE7',
                'barcode' => '8992775010012',
                'name' => 'Pulpen Standard AE7 Hitam 0.5mm',
                'category_id' => $catAtk->id,
                'unit' => 'pcs',
                'purchase_price' => 2200,
                'selling_price' => 3500,
                'stock' => 100,
                'minimum_stock' => 20,
                'is_active' => true,
            ],
        ];

        foreach ($products as $prodData) {
            $product = Product::updateOrCreate(
                ['sku' => $prodData['sku']],
                $prodData
            );

            // Jika ada stok awal, catat di mutasi stok
            if ($product->wasRecentlyCreated && $product->stock > 0 && $adminUser) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $adminUser->id,
                    'movement_type' => 'masuk',
                    'reference_type' => 'initial_seed',
                    'reference_id' => $product->id,
                    'quantity_in' => $product->stock,
                    'quantity_out' => 0,
                    'stock_before' => 0,
                    'stock_after' => $product->stock,
                    'notes' => 'Stok awal sistem',
                    'created_at' => now(),
                ]);
            }
        }
    }
}
