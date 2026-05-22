<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CreditAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WastageEntry;
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

        return view('modules.inventory', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'movements' => StockMovement::query()->with('product')->latest()->limit(12)->get(),
            'summary' => [
                'products' => $products->count(),
                'menu_items' => $products->whereIn('product_type', ['finished_product', 'resale_item'])->count(),
                'raw_materials' => $products->where('product_type', 'raw_material')->count(),
                'low_stock' => $products->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level && in_array($product->product_type, ['raw_material', 'resale_item', 'semi_finished'], true))->count(),
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
        return view('modules.kds', ['items' => OrderItem::query()->with('order.table')->whereIn('kitchen_status', ['pending', 'preparing'])->latest()->get()]);
    }

    public function recipes()
    {
        return view('modules.recipes', [
            'recipes' => Recipe::query()->with('product', 'items.ingredient')->latest()->get(),
            'finished' => Product::query()->whereIn('product_type', ['finished_product', 'semi_finished'])->orderBy('name')->get(),
            'ingredients' => Product::query()->whereIn('product_type', ['raw_material', 'semi_finished'])->orderBy('name')->get(),
        ]);
    }

    public function purchases()
    {
        return view('modules.purchases', [
            'purchases' => Purchase::query()->with('items')->latest()->get(),
            'products' => Product::query()->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished'])->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
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
