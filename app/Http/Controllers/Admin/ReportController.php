<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Laporan Penjualan (Tabel, Filter, Ekspor CSV, Cetak).
     */
    public function sales(Request $request): View|StreamedResponse
    {
        $query = Sale::with(['user', 'items']);

        // Filter Rentang Waktu
        $preset = $request->input('preset', 'this_month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($preset === 'today') {
            $startDate = now()->toDateString();
            $endDate = now()->toDateString();
        } elseif ($preset === 'yesterday') {
            $startDate = now()->subDay()->toDateString();
            $endDate = now()->subDay()->toDateString();
        } elseif ($preset === 'this_week') {
            $startDate = now()->startOfWeek()->toDateString();
            $endDate = now()->endOfWeek()->toDateString();
        } elseif ($preset === 'this_month') {
            $startDate = now()->startOfMonth()->toDateString();
            $endDate = now()->endOfMonth()->toDateString();
        }

        if ($startDate && $endDate) {
            $query->whereBetween('sold_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Ekspor ke CSV
        if ($request->input('export') === 'csv') {
            return $this->exportSalesToCsv($query->latest('sold_at')->get());
        }

        // Cetak Laporan
        if ($request->boolean('print')) {
            $sales = $query->latest('sold_at')->get();
            return view('admin.reports.sales_print', compact('sales', 'startDate', 'endDate'));
        }

        // Ringkasan
        $summaryQuery = clone $query;
        $totalSales = (float) $summaryQuery->sum('total_amount');
        $totalTransactions = $summaryQuery->count();
        $totalUnits = (int) SaleItem::whereIn('sale_id', $summaryQuery->pluck('id'))->sum('quantity');

        $sales = $query->latest('sold_at')->paginate(15)->withQueryString();
        $cashiers = User::where('role', 'kasir')->orderBy('name')->get();

        return view('admin.reports.sales', compact(
            'sales',
            'cashiers',
            'preset',
            'startDate',
            'endDate',
            'totalSales',
            'totalTransactions',
            'totalUnits'
        ));
    }

    /**
     * Detail Transaksi Penjualan untuk Admin (Modal/View).
     */
    public function saleDetail(Sale $sale): View
    {
        $sale->load(['user', 'items']);
        return view('admin.reports.sale_detail', compact('sale'));
    }

    /**
     * Laporan Estimasi Keuntungan Kotor.
     */
    public function profit(Request $request): View|StreamedResponse
    {
        $query = Sale::with(['user', 'items']);

        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $query->whereBetween('sold_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59'
        ]);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Ekspor ke CSV
        if ($request->input('export') === 'csv') {
            return $this->exportProfitToCsv($query->latest('sold_at')->get());
        }

        // Cetak Laporan
        if ($request->boolean('print')) {
            $sales = $query->latest('sold_at')->get();
            return view('admin.reports.profit_print', compact('sales', 'startDate', 'endDate'));
        }

        $allSales = (clone $query)->get();
        $totalRevenue = 0.0;
        $totalHpp = 0.0;

        foreach ($allSales as $s) {
            $totalRevenue += (float) $s->total_amount;
            foreach ($s->items as $item) {
                $totalHpp += (float) ($item->purchase_price_snapshot * $item->quantity);
            }
        }

        $totalProfit = $totalRevenue - $totalHpp;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0.0;

        $sales = $query->latest('sold_at')->paginate(15)->withQueryString();
        $cashiers = User::where('role', 'kasir')->orderBy('name')->get();

        return view('admin.reports.profit', compact(
            'sales',
            'cashiers',
            'startDate',
            'endDate',
            'totalRevenue',
            'totalHpp',
            'totalProfit',
            'profitMargin'
        ));
    }

    /**
     * Laporan Posisi Stok & Nilai Persediaan.
     */
    public function stock(Request $request): View|StreamedResponse
    {
        $query = Product::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'low') {
                $query->lowStock();
            } elseif ($request->status === 'out') {
                $query->outOfStock();
            }
        }

        // Ekspor ke CSV
        if ($request->input('export') === 'csv') {
            return $this->exportStockToCsv($query->orderBy('name')->get());
        }

        // Cetak Laporan
        if ($request->boolean('print')) {
            $products = $query->orderBy('name')->get();
            return view('admin.reports.stock_print', compact('products'));
        }

        // Ringkasan nilai aset persediaan
        $allProducts = (clone $query)->get();
        $totalSku = $allProducts->count();
        $totalUnits = $allProducts->sum('stock');
        $totalAssetValue = $allProducts->sum(function ($p) {
            return $p->stock * $p->purchase_price;
        });

        $products = $query->orderBy('name')->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.reports.stock', compact(
            'products',
            'categories',
            'totalSku',
            'totalUnits',
            'totalAssetValue'
        ));
    }

    /**
     * Helper Streamed CSV: Laporan Penjualan.
     */
    protected function exportSalesToCsv($sales): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan_penjualan_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            // Add BOM for Indonesian / Excel Unicode support
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['No. Faktur', 'Tanggal', 'Kasir', 'Jumlah Item', 'Subtotal (Rp)', 'Diskon (Rp)', 'Total (Rp)', 'Metode Bayar', 'Status']);

            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sold_at->format('Y-m-d H:i:s'),
                    $sale->user->name ?? '-',
                    $sale->items->sum('quantity'),
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->total_amount,
                    strtoupper($sale->payment_method),
                    strtoupper($sale->status),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper Streamed CSV: Laporan Keuntungan Kotor.
     */
    protected function exportProfitToCsv($sales): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan_keuntungan_kotor_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['No. Faktur', 'Tanggal', 'Kasir', 'Total Penjualan (Rp)', 'Total HPP (Rp)', 'Keuntungan Kotor (Rp)', 'Margin (%)']);

            foreach ($sales as $sale) {
                $hpp = 0.0;
                foreach ($sale->items as $item) {
                    $hpp += (float) ($item->purchase_price_snapshot * $item->quantity);
                }
                $profit = (float) $sale->total_amount - $hpp;
                $margin = $sale->total_amount > 0 ? ($profit / $sale->total_amount) * 100 : 0;

                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->sold_at->format('Y-m-d H:i:s'),
                    $sale->user->name ?? '-',
                    $sale->total_amount,
                    $hpp,
                    $profit,
                    number_format($margin, 2) . '%',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Helper Streamed CSV: Laporan Stok.
     */
    protected function exportStockToCsv($products): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="laporan_stok_barang_' . now()->format('Ymd_His') . '.csv"',
        ];

        return response()->stream(function () use ($products) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['Kode SKU', 'Barcode', 'Nama Barang', 'Kategori', 'Satuan', 'Stok Saat Ini', 'Batas Minimum', 'Harga Beli (Rp)', 'Harga Jual (Rp)', 'Total Nilai Aset (Rp)', 'Status']);

            foreach ($products as $p) {
                $status = $p->stock <= 0 ? 'HABIS' : ($p->stock <= $p->minimum_stock ? 'MENIPIS' : 'NORMAL');
                fputcsv($handle, [
                    $p->sku,
                    $p->barcode ?? '-',
                    $p->name,
                    $p->category->name ?? '-',
                    $p->unit,
                    $p->stock,
                    $p->minimum_stock,
                    $p->purchase_price,
                    $p->selling_price,
                    $p->stock * $p->purchase_price,
                    $status,
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
