<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Exception;

class SalesService
{
    public function __construct(
        protected StockService $stockService
    ) {}

    /**
     * Proses transaksi penjualan kasir secara atomik.
     *
     * @param array $items Array of ['product_id' => int, 'quantity' => int]
     * @param float $amountPaid Nominal yang dibayarkan pelanggan
     * @param string $paymentMethod 'tunai', 'qris', atau 'transfer'
     * @param int $userId ID Kasir
     * @param float $discountAmount Diskon transaksi jika ada
     * @param string|null $notes Catatan tambahan
     * @return Sale
     * @throws Exception
     */
    public function processSale(
        array $items,
        float $amountPaid,
        string $paymentMethod,
        int $userId,
        float $discountAmount = 0.0,
        ?string $notes = null
    ): Sale {
        if (empty($items)) {
            throw new Exception("Keranjang belanja kosong. Silakan tambahkan barang terlebih dahulu.");
        }

        return DB::transaction(function () use ($items, $amountPaid, $paymentMethod, $userId, $discountAmount, $notes) {
            $totalSubtotal = 0.0;
            $preparedSaleItems = [];

            // 1. Validasi, Kunci Stok (lockForUpdate), dan Hitung Ulang Total di Server
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $quantity = (int) ($item['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new Exception("Jumlah kuantitas barang harus minimal 1.");
                }

                // Row-level lock untuk menghindari overselling / race condition antar kasir
                $product = Product::where('id', $productId)->lockForUpdate()->first();

                if (!$product) {
                    throw new Exception("Produk dengan ID #{$productId} tidak ditemukan di sistem.");
                }

                if (!$product->is_active) {
                    throw new Exception("Produk '{$product->name}' sedang berstatus nonaktif dan tidak dapat dijual.");
                }

                if ($product->stock < $quantity) {
                    throw new Exception("Stok untuk '{$product->name}' tidak mencukupi. Tersedia: {$product->stock} {$product->unit}, diminta: {$quantity} {$product->unit}.");
                }

                // Hitung subtotal berdasarkan harga jual resmi di database server
                $sellingPrice = (float) $product->selling_price;
                $itemSubtotal = $sellingPrice * $quantity;
                $totalSubtotal += $itemSubtotal;

                $preparedSaleItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'selling_price' => $sellingPrice,
                    'purchase_price_snapshot' => (float) $product->purchase_price,
                    'item_discount' => 0.0,
                    'subtotal' => $itemSubtotal,
                ];
            }

            // Hitung total akhir
            $discountAmount = max(0.0, $discountAmount);
            $totalAmount = max(0.0, $totalSubtotal - $discountAmount);

            // Validasi uang bayar
            if ($amountPaid < $totalAmount) {
                $shortage = $totalAmount - $amountPaid;
                throw new Exception("Uang pembayaran kurang Rp " . number_format($shortage, 0, ',', '.') . ".");
            }

            $changeAmount = $amountPaid - $totalAmount;

            // Generate nomor faktur penjualan unik: INV-YYYYMMDD-XXXX
            $today = now()->format('Ymd');
            $countToday = Sale::whereDate('created_at', now()->toDateString())->count() + 1;
            $invoiceNumber = 'INV-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            // 2. Simpan Transaksi Penjualan Header
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $userId,
                'sold_at' => now(),
                'subtotal' => $totalSubtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'payment_method' => in_array($paymentMethod, ['tunai', 'qris', 'transfer']) ? $paymentMethod : 'tunai',
                'status' => 'selesai',
                'notes' => $notes,
            ]);

            // 3. Simpan Detail Item, Kurangi Stok, dan Catat Pergerakan Stok
            foreach ($preparedSaleItems as $prep) {
                /** @var Product $product */
                $product = $prep['product'];
                $quantity = $prep['quantity'];

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name_snapshot' => $product->name,
                    'sku_snapshot' => $product->sku,
                    'quantity' => $quantity,
                    'selling_price' => $prep['selling_price'],
                    'purchase_price_snapshot' => $prep['purchase_price_snapshot'],
                    'discount_amount' => $prep['item_discount'],
                    'subtotal' => $prep['subtotal'],
                ]);

                // Kurangi stok barang
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore - $quantity;
                $product->stock = $stockAfter;
                $product->save();

                // Catat mutasi stok keluar
                $this->stockService->recordMovement(
                    productId: $product->id,
                    userId: $userId,
                    movementType: 'keluar',
                    referenceType: 'sale',
                    referenceId: $sale->id,
                    qtyIn: 0,
                    qtyOut: $quantity,
                    stockBefore: $stockBefore,
                    stockAfter: $stockAfter,
                    notes: "Penjualan Faktur No. {$invoiceNumber}"
                );
            }

            return $sale->load(['items', 'user']);
        });
    }
}
