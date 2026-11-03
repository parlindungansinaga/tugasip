<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->lowStock();
            } elseif ($request->stock_status === 'out') {
                $query->outOfStock();
            }
        }

        $products = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('gudang.products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('gudang.products.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'initial_stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'sku.unique' => 'SKU / Kode Barang sudah digunakan.',
            'barcode.unique' => 'Barcode sudah terdaftar pada produk lain.',
            'selling_price.min' => 'Harga jual tidak boleh bernilai negatif.',
            'purchase_price.min' => 'Harga beli tidak boleh bernilai negatif.',
            'initial_stock.min' => 'Stok awal tidak boleh bernilai negatif.',
        ]);

        $initialStock = (int) $validated['initial_stock'];

        $product = Product::create([
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'] ?: null,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'unit' => $validated['unit'],
            'purchase_price' => $validated['purchase_price'],
            'selling_price' => $validated['selling_price'],
            'stock' => $initialStock,
            'minimum_stock' => $validated['minimum_stock'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Catat stok awal jika ada
        if ($initialStock > 0) {
            $this->stockService->recordMovement(
                productId: $product->id,
                userId: Auth::id(),
                movementType: 'masuk',
                referenceType: 'initial_stock',
                referenceId: $product->id,
                qtyIn: $initialStock,
                qtyOut: 0,
                stockBefore: 0,
                stockAfter: $initialStock,
                notes: 'Pencatatan stok awal barang baru'
            );
        }

        return redirect()->route('gudang.products.index')
            ->with('success', "Barang '{$product->name}' berhasil ditambahkan.");
    }

    public function show(Product $product): View
    {
        $product->load('category');
        $movements = $product->stockMovements()
            ->with('user')
            ->latest('created_at')
            ->paginate(15);

        return view('gudang.products.show', compact('product', 'movements'));
    }

    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('gudang.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,' . $product->id],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode,' . $product->id],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'unit' => ['required', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $product->update([
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'] ?: null,
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'unit' => $validated['unit'],
            'purchase_price' => $validated['purchase_price'],
            'selling_price' => $validated['selling_price'],
            'minimum_stock' => $validated['minimum_stock'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('gudang.products.index')
            ->with('success', "Data barang '{$product->name}' berhasil diperbarui.");
    }

    public function toggleStatus(Product $product): RedirectResponse
    {
        $product->is_active = !$product->is_active;
        $product->save();

        $statusStr = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Status barang '{$product->name}' berhasil {$statusStr}.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Periksa integritas data: jika produk sudah pernah ada di transaksi penjualan, tolak hard delete
        if ($product->saleItems()->exists()) {
            return back()->with('error', "Barang '{$product->name}' tidak boleh dihapus karena memiliki riwayat transaksi penjualan. Silakan nonaktifkan status barang.");
        }

        if ($product->goodsReceiptItems()->exists() || $product->stockMovements()->count() > 1) {
            return back()->with('error', "Barang memiliki riwayat pergerakan stok. Untuk menjaga audit trail, nonaktifkan barang daripada menghapusnya.");
        }

        $product->stockMovements()->delete();
        $product->delete();

        return redirect()->route('gudang.products.index')
            ->with('success', "Barang berhasil dihapus secara permanen.");
    }
}
