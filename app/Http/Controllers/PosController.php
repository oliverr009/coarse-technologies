<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\PosService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $selectedOrderId = (int) $request->integer('order');
        $openOrders = Order::query()
            ->with(['table', 'customer', 'items'])
            ->whereIn('status', ['held', 'sent'])
            ->latest()
            ->limit(12)
            ->get();

        if ($selectedOrderId && !$openOrders->contains('id', $selectedOrderId)) {
            $selectedOrder = Order::query()
                ->with(['table', 'customer', 'items'])
                ->whereIn('status', ['held', 'sent'])
                ->find($selectedOrderId);

            if ($selectedOrder) {
                $openOrders->prepend($selectedOrder);
            }
        }

        return view('pos.index', [
            'products' => Product::query()
                ->with('category')
                ->where('is_active', true)
                ->whereIn('product_type', ['finished_product', 'resale_item'])
                ->orderBy('category_id')
                ->orderBy('subcategory')
                ->orderBy('name')
                ->get(),
            'tables' => RestaurantTable::query()->orderBy('name')->get(),
            'customers' => Customer::query()->orderBy('name')->get(),
            'openOrders' => $openOrders,
            'selectedOrderId' => (string) ($selectedOrderId ?: ''),
            'taxRate' => (float) (Setting::query()->where('key', 'tax_rate')->first()?->value ?? 0),
        ]);
    }

    public function orders(Request $request)
    {
        $orders = Order::query()
            ->with(['table', 'customer', 'items'])
            ->whereIn('status', ['held', 'sent'])
            ->when($request->filled('status') && $request->string('status')->toString() !== 'active', fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('type') && $request->string('type')->toString() !== 'all', fn ($query) => $query->where('order_type', $request->string('type')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = '%' . trim((string) $request->string('search')) . '%';
                $query->where(function ($nested) use ($term) {
                    $nested->where('order_number', 'like', $term)
                        ->orWhere('notes', 'like', $term)
                        ->orWhereHas('table', fn ($table) => $table->where('name', 'like', $term))
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $term));
                });
            })
            ->latest()
            ->get();

        $selected = $orders->firstWhere('id', $request->integer('preview'))
            ?? $orders->first();

        return view('orders.index', [
            'orders' => $orders,
            'selected' => $selected,
            'counts' => [
                'all' => $orders->count(),
                'dine_in' => $orders->where('order_type', 'dine_in')->count(),
                'takeaway' => $orders->where('order_type', 'takeaway')->count(),
                'delivery' => $orders->where('order_type', 'delivery')->count(),
            ],
        ]);
    }

    public function postSale(Request $request, PosService $pos)
    {
        $data = $request->validate([
            'cart_json' => ['required', 'json'],
            'payments_json' => ['nullable', 'json'],
            'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'restaurant_table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'discount_type' => ['required', 'in:fixed,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'service_charge_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = collect(json_decode($data['cart_json'], true) ?: [])
            ->filter(fn ($line) => ((float) ($line['qty'] ?? 0)) > 0)
            ->values()
            ->all();
        $payments = json_decode($data['payments_json'] ?? '[]', true) ?: [];

        try {
            $sale = $pos->postSale($cart, $data + ['payments' => $payments], $request->user()->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['pos' => $e->getMessage()])->withInput();
        }

        return redirect()->route('pos.receipt', $sale)->with('status', "Sale posted: {$sale->sale_number}");
    }

    public function sendToKitchen(Request $request, PosService $pos)
    {
        $data = $request->validate([
            'cart_json' => ['required', 'json'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
            'restaurant_table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'covers' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = collect(json_decode($data['cart_json'], true) ?: [])
            ->filter(fn ($line) => ((float) ($line['qty'] ?? 0)) > 0)
            ->values()
            ->all();

        try {
            $order = $pos->sendToKitchen($cart, $data, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['pos' => $e->getMessage()])->withInput();
        }

        return redirect()->route('orders', ['preview' => $order->id])->with('status', "Sent to kitchen: {$order->order_number}");
    }

    public function holdOrder(Request $request, PosService $pos)
    {
        $data = $request->validate([
            'cart_json' => ['required', 'json'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'order_type' => ['required', 'in:dine_in,takeaway,delivery'],
            'restaurant_table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'covers' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cart = collect(json_decode($data['cart_json'], true) ?: [])
            ->filter(fn ($line) => ((float) ($line['qty'] ?? 0)) > 0)
            ->values()
            ->all();

        try {
            $order = $pos->holdOrder($cart, $data, $request->user()->id);
        } catch (\Throwable $e) {
            return back()->withErrors(['pos' => $e->getMessage()])->withInput();
        }

        return redirect()->route('orders', ['preview' => $order->id])->with('status', "Order held: {$order->order_number}");
    }

    public function receipt(Sale $sale)
    {
        return view('pos.receipt', [
            'sale' => $sale->load(['items', 'payments', 'table', 'customer', 'cashier']),
        ]);
    }
}
