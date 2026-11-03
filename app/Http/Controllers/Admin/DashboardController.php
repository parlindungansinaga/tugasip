<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        // 1. Kartu Statistik Hari Ini
        $salesTodayQuery = Sale::whereDate('sold_at', $today);
        $totalSalesToday = (float) $salesTodayQuery->sum('total_amount');
        $transactionCountToday = $salesTodayQuery->count();

        $unitsSoldToday = (int) SaleItem::whereHas('sale', function ($q) use ($today) {
            $q->whereDate('sold_at', $today);
        })->sum('quantity');

        // Estimasi Laba Kotor Hari Ini (subtotal - (qty * hpp_snapshot))
        $grossProfitToday = (float) SaleItem::whereHas('sale', function ($q) use ($today) {
            $q->whereDate('sold_at', $today);
        })->selectRaw('SUM(subtotal - (purchase_price_snapshot * quantity)) as profit')
          ->value('profit') ?? 0.0;

        $activeProductsCount = Product::where('is_active', true)->count();
        $lowStockCount = Product::where('is_active', true)->lowStock()->count();

        // 2. Data Grafik Penjualan 7 Hari Terakhir
        $chartDates = [];
        $salesTrend = [];
        $profitTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $label = now()->subDays($i)->format('d M');
            $chartDates[] = $label;

            $daySale = (float) Sale::whereDate('sold_at', $date)->sum('total_amount');
            $salesTrend[] = $daySale;

            $dayProfit = (float) SaleItem::whereHas('sale', function ($q) use ($date) {
                $q->whereDate('sold_at', $date);
            })->selectRaw('SUM(subtotal - (purchase_price_snapshot * quantity)) as profit')
              ->value('profit') ?? 0.0;
            $profitTrend[] = $dayProfit;
        }

        // 3. Top 5 Produk Terlaris Bulan Ini
        $topProducts = SaleItem::select(
                'product_name_snapshot',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(subtotal) as total_sales')
            )
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->groupBy('product_name_snapshot')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // 4. 5 Transaksi Terakhir
        $recentTransactions = Sale::with(['user', 'items'])
            ->latest('sold_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSalesToday',
            'transactionCountToday',
            'unitsSoldToday',
            'grossProfitToday',
            'activeProductsCount',
            'lowStockCount',
            'chartDates',
            'salesTrend',
            'profitTrend',
            'topProducts',
            'recentTransactions'
        ));
    }
}
