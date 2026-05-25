<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CreditAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosAuditLog;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\RestaurantTable;
use App\Models\SaleAdjustment;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    public function inventory()
    {
        $products = Product::query()
            ->with('category')
            ->withSum('stockLevels as stock_qty', 'quantity')
            ->orderBy('name')
            ->get();

        $trackedProducts = $products->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished']);
        $stockValue = $trackedProducts->sum(function ($product) {
            return (float) ($product->stock_qty ?? 0) * (float) ($product->cost_price ?? 0);
        });

        return view('modules.inventory', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'movements' => StockMovement::query()->with('product')->latest()->limit(18)->get(),
            'adjustments' => InventoryAdjustment::query()->with(['product', 'actor'])->latest()->limit(12)->get(),
            'lowStock' => $trackedProducts
                ->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)
                ->sortBy('stock_qty')
                ->take(8)
                ->values(),
            'negativeStock' => $trackedProducts
                ->filter(fn ($product) => (float) ($product->stock_qty ?? 0) < 0)
                ->sortBy('stock_qty')
                ->take(8)
                ->values(),
            'summary' => [
                'products' => $products->count(),
                'menu_items' => $products->whereIn('product_type', ['finished_product', 'resale_item'])->count(),
                'raw_materials' => $products->where('product_type', 'raw_material')->count(),
                'low_stock' => $trackedProducts->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)->count(),
                'negative_stock' => $trackedProducts->filter(fn ($product) => (float) ($product->stock_qty ?? 0) < 0)->count(),
                'stock_value' => $stockValue,
                'adjustments' => InventoryAdjustment::query()->count(),
            ],
        ]);
    }

    public function tables()
    {
        return view('modules.tables', [
            'tables' => RestaurantTable::query()
                ->with(['orders' => fn ($query) => $query->with('items')->whereIn('status', ['held', 'sent'])->latest()])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function kds()
    {
        $items = OrderItem::query()
            ->with(['order.table'])
            ->whereIn('kitchen_status', ['pending', 'preparing', 'ready'])
            ->latest()
            ->get();

        return view('modules.kds', [
            'items' => $items,
            'summary' => [
                'pending' => $items->where('kitchen_status', 'pending')->count(),
                'preparing' => $items->where('kitchen_status', 'preparing')->count(),
                'ready' => $items->where('kitchen_status', 'ready')->count(),
                'tables' => $items->pluck('order.restaurant_table_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function recipes()
    {
        $recipes = Recipe::query()->with('product', 'items.ingredient')->latest()->get();

        return view('modules.recipes', [
            'recipes' => $recipes,
            'finished' => Product::query()->whereIn('product_type', ['finished_product', 'semi_finished'])->orderBy('name')->get(),
            'ingredients' => Product::query()->whereIn('product_type', ['raw_material', 'semi_finished'])->orderBy('name')->get(),
            'productionRuns' => ProductionRun::query()->with(['recipe.product', 'actor'])->latest()->limit(18)->get(),
            'summary' => [
                'recipes' => $recipes->count(),
                'active_recipes' => $recipes->where('status', 'active')->count(),
                'ingredients' => Product::query()->whereIn('product_type', ['raw_material', 'semi_finished'])->count(),
                'production_runs' => ProductionRun::query()->count(),
            ],
        ]);
    }

    public function purchases()
    {
        $purchases = Purchase::query()->with(['items.product', 'supplier'])->latest()->get();
        $thisMonth = now()->startOfMonth();
        $topSuppliers = $purchases
            ->filter(fn ($purchase) => $purchase->supplier)
            ->groupBy('supplier_id')
            ->map(function ($rows) {
                $supplier = $rows->first()->supplier;

                return (object) [
                    'supplier' => $supplier,
                    'purchase_count' => $rows->count(),
                    'total_amount' => (float) $rows->sum('total_amount'),
                    'last_purchase_at' => $rows->max('created_at'),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(6)
            ->values();

        return view('modules.purchases', [
            'purchases' => $purchases,
            'products' => Product::query()->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished'])->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'movements' => StockMovement::query()->with('product')->where('movement_type', 'PURCHASE')->latest()->limit(12)->get(),
            'topSuppliers' => $topSuppliers,
            'summary' => [
                'purchases' => $purchases->count(),
                'value' => $purchases->sum('total_amount'),
                'items' => $purchases->sum(fn ($purchase) => $purchase->items->count()),
                'suppliers' => Supplier::query()->count(),
                'month_value' => (float) $purchases->where('created_at', '>=', $thisMonth)->sum('total_amount'),
                'avg_receipt' => (float) ($purchases->count() ? ($purchases->sum('total_amount') / $purchases->count()) : 0),
            ],
        ]);
    }

    public function expenses()
    {
        return view('modules.expenses', ['expenses' => Expense::query()->latest()->get()]);
    }

    public function credit()
    {
        return view('modules.credit', [
            'customers' => Customer::query()->orderBy('name')->get(),
            'credits' => CreditAccount::query()->with('customer')->latest()->get(),
        ]);
    }

    public function reports()
    {
        return view('modules.reports', [
            'movements' => StockMovement::query()->with('product')->latest()->limit(80)->get(),
            'consumption' => StockMovement::query()
                ->join('products', 'products.id', '=', 'stock_movements.product_id')
                ->where('movement_type', 'SALE_CONSUMPTION')
                ->selectRaw('products.name, products.unit, SUM(ABS(stock_movements.quantity)) qty, SUM(COALESCE(stock_movements.total_cost,0)) cost')
                ->groupBy('products.name', 'products.unit')
                ->orderByDesc('qty')
                ->get(),
            'expenses' => Expense::query()->sum('amount'),
            'auditLogs' => PosAuditLog::query()->with(['actor', 'approver', 'order', 'sale'])->latest()->limit(20)->get(),
            'saleAdjustments' => SaleAdjustment::query()->with(['sale', 'actor', 'approver', 'items'])->latest()->limit(20)->get(),
            'inventoryAdjustments' => InventoryAdjustment::query()->with(['product', 'actor'])->latest()->limit(20)->get(),
        ]);
    }

    public function users()
    {
        return view('modules.users', ['users' => User::query()->orderBy('name')->get()]);
    }

    public function settings()
    {
        return view('modules.settings', ['settings' => Setting::query()->pluck('value', 'key')]);
    }

    public function hotel()
    {
        return view('modules.hotel');
    }
}
