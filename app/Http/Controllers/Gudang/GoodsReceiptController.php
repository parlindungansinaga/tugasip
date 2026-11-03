<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\StockService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(Request $request): View
    {
        $query = GoodsReceipt::with(['supplier', 'user', 'items']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('received_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $receipts = $query->latest('received_at')->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('gudang.receipts.index', compact('receipts', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('gudang.receipts.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ], [
            'items.required' => 'Minimal tambahkan 1 barang dalam penerimaan.',
            'items.*.quantity.min' => 'Jumlah barang masuk minimal 1.',
            'items.*.purchase_price.min' => 'Harga beli barang masuk tidak boleh negatif.',
        ]);

        try {
            $goodsReceipt = $this->stockService->processGoodsReceipt(
                receiptData: [
                    'supplier_id' => $request->supplier_id,
                    'received_at' => $request->received_at,
                    'notes' => $request->notes,
                ],
                items: $request->items,
                userId: Auth::id()
            );

            return redirect()->route('gudang.receipts.show', $goodsReceipt)
                ->with('success', "Penerimaan barang {$goodsReceipt->receipt_number} berhasil dicatat dan stok telah bertambah.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses penerimaan: ' . $e->getMessage());
        }
    }

    public function show(GoodsReceipt $receipt): View
    {
        $receipt->load(['supplier', 'user', 'items.product']);
        return view('gudang.receipts.show', compact('receipt'));
    }
}
