<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\StockMovement;

class ReportService
{
    public function dashboard(): array
    {
        return [
            'sales_today' => Sale::query()->whereDate('created_at', today())->sum('total_amount'),
            'bills_today' => Sale::query()->whereDate('created_at', today())->count(),
            'expenses_month' => Expense::query()->whereMonth('created_at', now()->month)->sum('amount'),
            'ingredient_moves' => StockMovement::query()->where('movement_type', 'SALE_CONSUMPTION')->count(),
        ];
    }
}

