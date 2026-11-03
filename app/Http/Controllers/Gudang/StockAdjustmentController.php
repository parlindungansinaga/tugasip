<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {}

    public function index(Request $request): View
    {
        $query = StockAdjustment::with(['user', 'items.product']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('adjusted_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $adjustments = $query->latest('adjusted_at')->paginate(15)->withQueryString();

        return view('gudang.adjustments.index', compact('adjustments'));
    }

    public function create(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('gudang.adjustments.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'adjusted_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.stock_after' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['required', 'string', 'max:255'],
        ], [
            'items.required' => 'Minimal pilih 1 barang untuk disesuaikan.',
            'items.*.stock_after.min' => 'Stok fisik hasil penyesuaian tidak boleh negatif.',
            'items.*.reason.required' => 'Alasan penyesuaian stok wajib diisi.',
        ]);

        try {
            $adjustment = $this->stockService->processStockAdjustment(
                adjustmentData: [
                    'adjusted_at' => $request->adjusted_at,
                    'notes' => $request->notes,
                ],
                items: $request->items,
                userId: Auth::id()
            );

            return redirect()->route('gudang.adjustments.show', $adjustment)
                ->with('success', "Penyesuaian stok No. {$adjustment->adjustment_number} berhasil dicatat.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses penyesuaian: ' . $e->getMessage());
        }
    }

    public function show(StockAdjustment $adjustment): View
    {
        $adjustment->load(['user', 'items.product']);
        return view('gudang.adjustments.show', compact('adjustment'));
    }
}
