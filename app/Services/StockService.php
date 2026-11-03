<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * Catat pergerakan stok ke tabel audit log stock_movements.
     */
    public function recordMovement(
        int $productId,
        int $userId,
        string $movementType,
        string $referenceType,
        ?int $referenceId,
        int $qtyIn,
        int $qtyOut,
        int $stockBefore,
        int $stockAfter,
        ?string $notes = null
    ): StockMovement {
        return StockMovement::create([
            'product_id' => $productId,
            'user_id' => $userId,
            'movement_type' => $movementType,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'quantity_in' => $qtyIn,
            'quantity_out' => $qtyOut,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Proses penerimaan barang masuk dari supplier (Goods Receipt).
     */
    public function processGoodsReceipt(array $receiptData, array $items, int $userId): GoodsReceipt
    {
        return DB::transaction(function () use ($receiptData, $items, $userId) {
            // Generate nomor penerimaan unik: GR-YYYYMMDD-XXXX
            $today = now()->format('Ymd');
            $countToday = GoodsReceipt::whereDate('created_at', now()->toDateString())->count() + 1;
            $receiptNumber = 'GR-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $goodsReceipt = GoodsReceipt::create([
                'receipt_number' => $receiptNumber,
                'supplier_id' => $receiptData['supplier_id'] ?? null,
                'user_id' => $userId,
                'received_at' => $receiptData['received_at'] ?? now(),
                'notes' => $receiptData['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                // Kunci baris produk
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Produk dengan ID {$item['product_id']} tidak ditemukan.");
                }

                $quantity = (int) $item['quantity'];
                if ($quantity <= 0) {
                    throw new Exception("Jumlah barang masuk untuk {$product->name} harus lebih dari 0.");
                }

                $purchasePrice = (float) $item['purchase_price'];
                $stockBefore = $product->stock;
                $stockAfter = $stockBefore + $quantity;

                // Update stok produk & update harga beli terkini jika diatur
                $product->stock = $stockAfter;
                if ($purchasePrice > 0) {
                    $product->purchase_price = $purchasePrice;
                }
                $product->save();

                // Simpan item penerimaan
                GoodsReceiptItem::create([
                    'goods_receipt_id' => $goodsReceipt->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'purchase_price' => $purchasePrice,
                ]);

                // Catat mutasi stok
                $this->recordMovement(
                    productId: $product->id,
                    userId: $userId,
                    movementType: 'masuk',
                    referenceType: 'goods_receipt',
                    referenceId: $goodsReceipt->id,
                    qtyIn: $quantity,
                    qtyOut: 0,
                    stockBefore: $stockBefore,
                    stockAfter: $stockAfter,
                    notes: "Penerimaan No. {$receiptNumber}"
                );
            }

            return $goodsReceipt;
        });
    }

    /**
     * Proses koreksi / penyesuaian stok fisik (Stock Adjustment).
     */
    public function processStockAdjustment(array $adjustmentData, array $items, int $userId): StockAdjustment
    {
        return DB::transaction(function () use ($adjustmentData, $items, $userId) {
            $today = now()->format('Ymd');
            $countToday = StockAdjustment::whereDate('created_at', now()->toDateString())->count() + 1;
            $adjNumber = 'ADJ-' . $today . '-' . str_pad($countToday, 4, '0', STR_PAD_LEFT);

            $adjustment = StockAdjustment::create([
                'adjustment_number' => $adjNumber,
                'user_id' => $userId,
                'adjusted_at' => $adjustmentData['adjusted_at'] ?? now(),
                'notes' => $adjustmentData['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                if (!$product) {
                    throw new Exception("Produk tidak ditemukan.");
                }

                $stockBefore = $product->stock;
                $stockAfter = (int) $item['stock_after'];

                if ($stockAfter < 0) {
                    throw new Exception("Stok fisik baru untuk {$product->name} tidak boleh negatif.");
                }

                $diff = $stockAfter - $stockBefore;
                $reason = $item['reason'] ?? 'Penyesuaian stok fisik';

                // Update stok produk
                $product->stock = $stockAfter;
                $product->save();

                // Simpan detail koreksi
                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id' => $product->id,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'difference' => $diff,
                    'reason' => $reason,
                ]);

                // Catat mutasi stok
                $qtyIn = $diff > 0 ? $diff : 0;
                $qtyOut = $diff < 0 ? abs($diff) : 0;

                $this->recordMovement(
                    productId: $product->id,
                    userId: $userId,
                    movementType: 'penyesuaian',
                    referenceType: 'adjustment',
                    referenceId: $adjustment->id,
                    qtyIn: $qtyIn,
                    qtyOut: $qtyOut,
                    stockBefore: $stockBefore,
                    stockAfter: $stockAfter,
                    notes: "Penyesuaian No. {$adjNumber}: {$reason}"
                );
            }

            return $adjustment;
        });
    }
}
