<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::where('is_active', true)->count();
        $totalUnits = Product::where('is_active', true)->sum('stock');
        $lowStockCount = Product::where('is_active', true)->lowStock()->count();
        $outOfStockCount = Product::where('is_active', true)->outOfStock()->count();
        $receiptsThisMonth = GoodsReceipt::whereMonth('received_at', now()->month)
            ->whereYear('received_at', now()->year)
            ->count();
        $movementsThisMonth = StockMovement::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Daftar produk butuh restock
        $restockAlerts = Product::with('category')
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        // 10 Mutasi stok terbaru
        $recentMovements = StockMovement::with(['product', 'user'])
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('gudang.dashboard', compact(
            'totalProducts',
            'totalUnits',
            'lowStockCount',
            'outOfStockCount',
            'receiptsThisMonth',
            'movementsThisMonth',
            'restockAlerts',
            'recentMovements'
        ));
    }
}
