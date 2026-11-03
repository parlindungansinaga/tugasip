<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SalesService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(
        protected SalesService $salesService
    ) {}

    /**
     * Simpan transaksi penjualan kasir (Menerima AJAX POST).
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'amount_paid' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:tunai,qris,transfer'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'items.required' => 'Keranjang transaksi masih kosong.',
            'amount_paid.required' => 'Nominal uang pembayaran wajib diisi.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
        ]);

        try {
            $sale = $this->salesService->processSale(
                items: $request->items,
                amountPaid: (float) $request->amount_paid,
                paymentMethod: $request->payment_method,
                userId: Auth::id(),
                discountAmount: (float) ($request->discount_amount ?? 0),
                notes: $request->notes
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'invoice_number' => $sale->invoice_number,
                'sold_at' => $sale->sold_at->format('d/m/Y H:i'),
                'total_amount' => (float) $sale->total_amount,
                'amount_paid' => (float) $sale->amount_paid,
                'change_amount' => (float) $sale->change_amount,
                'receipt_url' => route('kasir.print', $sale->id),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Halaman cetak struk thermal 58mm / 80mm.
     */
    public function printReceipt(Sale $sale): View
    {
        // Kasir hanya boleh mencetak transaksi miliknya, kecuali role admin
        if (Auth::user()->isKasir() && $sale->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak struk transaksi kasir lain.');
        }

        $sale->load(['items.product', 'user']);
        return view('kasir.receipt', compact('sale'));
    }

    /**
     * Riwayat transaksi kasir yang sedang login.
     */
    public function history(Request $request): View
    {
        $query = Sale::with(['items', 'user'])
            ->where('user_id', Auth::id());

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sold_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', "%{$request->invoice_number}%");
        }

        $sales = $query->latest('sold_at')->paginate(15)->withQueryString();

        return view('kasir.history', compact('sales'));
    }
}
