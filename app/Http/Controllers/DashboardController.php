<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Services\ReportService;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke(ReportService $reports)
    {
        $weeklySales = collect(range(6, 0))
            ->map(function (int $daysAgo) {
                $date = today()->subDays($daysAgo);

                return [
                    'label' => $date->format('D'),
                    'total' => (float) Sale::query()->whereDate('created_at', $date)->sum('total_amount'),
                    'date' => $date,
                ];
            });

        $todaySales = Sale::query()->whereDate('created_at', today());
        $todayPaymentTotal = (float) SalePayment::query()
            ->whereHas('sale', fn ($query) => $query->whereDate('created_at', today()))
            ->sum('amount');
        $todayCreditTotal = (float) $todaySales->clone()->sum('balance_due');
        $paymentBase = max(0.01, $todayPaymentTotal + $todayCreditTotal);

        $paymentMix = [
            [
                'label' => 'M-Pesa',
                'amount' => (float) SalePayment::query()->where('method', 'mpesa')->whereHas('sale', fn ($query) => $query->whereDate('created_at', today()))->sum('amount'),
                'color' => 'var(--green)',
                'fill' => 'sf-green',
            ],
            [
                'label' => 'Cash',
                'amount' => (float) SalePayment::query()->where('method', 'cash')->whereHas('sale', fn ($query) => $query->whereDate('created_at', today()))->sum('amount'),
                'color' => 'var(--blue)',
                'fill' => 'sf-green',
                'fill_style' => 'background:var(--blue)',
            ],
            [
                'label' => 'Credit',
                'amount' => $todayCreditTotal,
                'color' => 'var(--gold)',
                'fill' => 'sf-gold',
            ],
        ];

        $paymentMix = collect($paymentMix)->map(function (array $row) use ($paymentBase) {
            $row['percent'] = round(($row['amount'] / $paymentBase) * 100);

            return $row;
        });

        $outstandingCredit = (float) Sale::query()->where('balance_due', '>', 0)->sum('balance_due');
        $openCreditCount = Sale::query()->where('balance_due', '>', 0)->count();
        $lowStockQuery = Product::query()
            ->join('stock_levels', 'stock_levels.product_id', '=', 'products.id')
            ->whereColumn('stock_levels.quantity', '<=', 'products.reorder_level');

        return view('dashboard', [
            'metrics' => $reports->dashboard(),
            'topProducts' => Sale::query()
                ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
                ->selectRaw('sale_items.product_name, SUM(sale_items.quantity) qty, SUM(sale_items.line_total) amount')
                ->groupBy('sale_items.product_name')
                ->orderByDesc('amount')
                ->limit(5)
                ->get(),
            'lowStock' => (clone $lowStockQuery)
                ->select('products.*', 'stock_levels.quantity')
                ->limit(5)
                ->get(),
            'lowStockCount' => (clone $lowStockQuery)->count(),
            'activity' => StockMovement::query()->with('product')->latest()->limit(6)->get(),
            'weeklySales' => $weeklySales,
            'weeklySalesMax' => max(1, (float) $weeklySales->max('total')),
            'paymentMix' => $paymentMix,
            'outstandingCredit' => $outstandingCredit,
            'openCreditCount' => $openCreditCount,
        ]);
    }
}
