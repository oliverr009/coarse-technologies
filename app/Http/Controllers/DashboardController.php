<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Services\ReportService;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports)
    {
        return view('dashboard', [
            'metrics' => $reports->dashboard(),
            'topProducts' => Sale::query()
                ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->selectRaw('sale_items.product_name, SUM(sale_items.quantity) qty, SUM(sale_items.line_total) amount')
                ->groupBy('sale_items.product_name')
                ->orderByDesc('amount')
                ->limit(5)
                ->get(),
            'lowStock' => Product::query()
                ->join('stock_levels', 'stock_levels.product_id', '=', 'products.id')
                ->whereColumn('stock_levels.quantity', '<=', 'products.reorder_level')
                ->select('products.*', 'stock_levels.quantity')
                ->limit(5)
                ->get(),
            'activity' => StockMovement::query()->with('product')->latest()->limit(6)->get(),
        ]);
    }
}

