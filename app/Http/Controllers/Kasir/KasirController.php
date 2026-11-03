<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KasirController extends Controller
{
    /**
     * Tampilan utama aplikasi Kasir POS.
     */
    public function index(): View
    {
        return view('kasir.dashboard');
    }

    /**
     * Cari produk via AJAX (Nama, SKU, atau Barcode).
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = trim($request->input('q', ''));

        if (empty($term)) {
            return response()->json([]);
        }

        $products = Product::where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('sku', 'like', "%{$term}%")
                  ->orWhere('barcode', 'like', "%{$term}%");
            })
            ->select('id', 'sku', 'barcode', 'name', 'selling_price', 'stock', 'unit')
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json($products);
    }

    /**
     * Cari produk persis berdasarkan scan barcode USB / Kamera.
     */
    public function findProductByBarcode(Request $request): JsonResponse
    {
        $code = trim($request->input('code', ''));

        if (empty($code)) {
            return response()->json(['found' => false, 'message' => 'Kode barcode kosong.'], 400);
        }

        $product = Product::where(function ($q) use ($code) {
            $q->where('barcode', $code)
              ->orWhere('sku', $code);
        })->first();

        if (!$product) {
            return response()->json([
                'found' => false,
                'message' => "Barang dengan barcode/kode '{$code}' tidak ditemukan di sistem."
            ], 404);
        }

        if (!$product->is_active) {
            return response()->json([
                'found' => false,
                'message' => "Barang '{$product->name}' sedang berstatus nonaktif."
            ], 422);
        }

        if ($product->stock <= 0) {
            return response()->json([
                'found' => false,
                'message' => "Stok barang '{$product->name}' habis (0 {$product->unit})."
            ], 422);
        }

        return response()->json([
            'found' => true,
            'product' => [
                'id' => $product->id,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'selling_price' => (float) $product->selling_price,
                'stock' => (int) $product->stock,
                'unit' => $product->unit,
            ]
        ]);
    }
}
