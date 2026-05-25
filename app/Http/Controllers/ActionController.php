<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WastageEntry;
use App\Services\InventoryService;
use App\Services\Numbers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ActionController extends Controller
{
    public function product(Request $request)
    {
        $data = $request->validate([
            'name' => ['required'],
            'sku' => ['nullable', 'max:80'],
            'barcode' => ['nullable', 'max:80'],
            'description' => ['nullable', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'new_category' => ['nullable', 'max:80'],
            'subcategory' => ['nullable', 'max:80'],
            'product_type' => ['required', 'in:raw_material,finished_product,resale_item,semi_finished,service'],
            'unit' => ['required', 'max:30'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (! empty($data['new_category'])) {
            $category = Category::query()->firstOrCreate([
                'name' => trim($data['new_category']),
            ], [
                'type' => in_array($data['product_type'], ['raw_material', 'semi_finished'], true) ? 'inventory' : 'menu',
            ]);
            $data['category_id'] = $category->id;
        }

        unset($data['new_category']);
        $data['is_active'] = $request->boolean('is_active', true);

        Product::query()->create($data);

        return back()->with('status', 'Product saved.');
    }

    public function recipe(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'yield_quantity' => ['required', 'numeric', 'gt:0'],
            'yield_unit' => ['required'],
            'ingredient_product_id' => ['array'],
        ]);

        DB::transaction(function () use ($request, $data) {
            DB::table('recipes')->where('product_id', $data['product_id'])->where('status', 'active')->update(['status' => 'archived']);
            $recipeId = DB::table('recipes')->insertGetId([
                'product_id' => $data['product_id'],
                'yield_quantity' => $data['yield_quantity'],
                'yield_unit' => $data['yield_unit'],
                'version' => (int) $request->input('version', 1),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->input('ingredient_product_id', []) as $i => $ingredientId) {
                if (! $ingredientId) {
                    continue;
                }
                DB::table('recipe_items')->insert([
                    'recipe_id' => $recipeId,
                    'ingredient_product_id' => $ingredientId,
                    'quantity_required' => $request->input("quantity_required.{$i}", 0),
                    'unit' => $request->input("unit.{$i}", 'kg'),
                    'wastage_percent' => $request->input("wastage_percent.{$i}", 0),
                    'cost_snapshot' => $request->input("cost_snapshot.{$i}") ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('status', 'Recipe activated.');
    }

    public function table(Request $request)
    {
        $data = $request->validate([
            'table_id' => ['required', 'exists:restaurant_tables,id'],
            'status' => ['required', 'in:available,occupied,reserved,needs_cleaning'],
        ]);

        RestaurantTable::query()->whereKey($data['table_id'])->update(['status' => $data['status']]);

        return back()->with('status', 'Table marked ' . str_replace('_', ' ', $data['status']) . '.');
    }

    public function kds(Request $request)
    {
        $data = $request->validate(['item_id' => ['required', 'exists:order_items,id'], 'status' => ['required', 'in:pending,preparing,ready']]);
        $item = OrderItem::query()->with('order.items')->findOrFail($data['item_id']);
        $item->update(['kitchen_status' => $data['status']]);

        $item->order->refresh()->load('items');
        $activeItems = $item->order->items->whereIn('kitchen_status', ['pending', 'preparing'])->count();
        if ($activeItems === 0) {
            $item->order->update(['status' => 'ready']);
        }

        return back()->with('status', 'Kitchen item updated.');
    }

    public function purchase(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'supplier_name' => ['nullable', 'max:120'],
            'supplier_phone' => ['nullable', 'max:80'],
            'supplier_email' => ['nullable', 'email', 'max:120'],
            'notes' => ['nullable', 'max:1000'],
            'product_id' => ['required', 'array'],
            'product_id.*' => ['nullable', 'exists:products,id'],
            'quantity' => ['required', 'array'],
            'quantity.*' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'array'],
            'unit_cost.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $inventory, $data) {
            $supplierId = $data['supplier_id'] ?? null;
            if (! $supplierId && ! empty($data['supplier_name'])) {
                $supplierId = Supplier::query()->firstOrCreate([
                    'name' => trim($data['supplier_name']),
                ], [
                    'phone' => $data['supplier_phone'] ?? null,
                    'email' => $data['supplier_email'] ?? null,
                ])->id;
            }

            $purchase = Purchase::query()->create([
                'purchase_number' => Numbers::next('PUR', 'purchases', 'purchase_number'),
                'supplier_id' => $supplierId,
                'outlet_id' => 1,
                'total_amount' => 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $total = 0;
            foreach ($data['product_id'] as $i => $productId) {
                if (! $productId) {
                    continue;
                }
                $qty = (float) ($data['quantity'][$i] ?? 0);
                $cost = (float) ($data['unit_cost'][$i] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $line = $qty * $cost;
                $total += $line;
                $purchase->items()->create(['product_id' => $productId, 'quantity' => $qty, 'unit_cost' => $cost, 'line_total' => $line]);
                $inventory->move((int) $productId, 1, $qty, 'PURCHASE', Purchase::class, $purchase->id, $request->user()->id, $cost, 'Purchase stock-in');
            }

            if ($total <= 0) {
                throw new \RuntimeException('Add at least one purchase line with quantity greater than zero.');
            }

            $purchase->update(['total_amount' => $total]);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['purchase' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Purchase posted.');
    }

    public function wastage(Request $request, InventoryService $inventory)
    {
        $data = $request->validate(['product_id' => ['required', 'exists:products,id'], 'quantity' => ['required', 'numeric', 'gt:0'], 'reason' => ['required'], 'notes' => ['nullable']]);
        DB::transaction(function () use ($data, $request, $inventory) {
            $entry = WastageEntry::query()->create([...$data, 'outlet_id' => 1, 'created_by' => $request->user()->id]);
            $inventory->move((int) $entry->product_id, 1, -abs((float) $entry->quantity), 'WASTAGE', WastageEntry::class, $entry->id, $request->user()->id, null, $entry->reason);
        });

        return back()->with('status', 'Wastage recorded.');
    }

    public function expense(Request $request)
    {
        Expense::query()->create([...$request->validate(['category' => ['required'], 'description' => ['required'], 'amount' => ['required', 'numeric'], 'payment_method' => ['required']]), 'created_by' => $request->user()->id]);
        return back()->with('status', 'Expense saved.');
    }

    public function customer(Request $request)
    {
        Customer::query()->create($request->validate(['name' => ['required'], 'phone' => ['nullable'], 'email' => ['nullable'], 'credit_limit' => ['nullable', 'numeric']]));
        return back()->with('status', 'Customer saved.');
    }

    public function user(Request $request)
    {
        User::query()->create([...$request->validate(['name' => ['required'], 'email' => ['required', 'email', 'unique:users,email'], 'role' => ['required'], 'password' => ['required']]), 'password' => Hash::make($request->input('password')), 'is_active' => true]);
        return back()->with('status', 'User saved.');
    }

    public function settings(Request $request)
    {
        foreach (['allow_negative_inventory', 'tax_rate', 'currency', 'business_name', 'kra_pin', 'discount_approval_threshold'] as $key) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $key === 'allow_negative_inventory' ? $request->boolean($key) : $request->input($key)]
            );
        }

        if ($request->filled('manager_override_pin')) {
            Setting::query()->updateOrCreate(
                ['key' => 'manager_override_pin'],
                ['value' => Hash::make((string) $request->input('manager_override_pin'))]
            );
        }

        return back()->with('status', 'Settings saved.');
    }
}
