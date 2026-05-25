<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CreditAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\InventoryAdjustment;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Purchase;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\ShiftCashEntry;
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

    public function production(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'recipe_id' => ['required', 'exists:recipes,id'],
            'planned_quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($data, $request, $inventory) {
                $recipe = \App\Models\Recipe::query()
                    ->with(['product', 'items.ingredient'])
                    ->findOrFail((int) $data['recipe_id']);

                if ($recipe->status !== 'active') {
                    throw new \RuntimeException('Only active recipes can be produced.');
                }

                if ($recipe->items->isEmpty()) {
                    throw new \RuntimeException('This recipe has no ingredients yet.');
                }

                $plannedQty = (float) $data['planned_quantity'];
                $yieldQty = (float) $recipe->yield_quantity;
                $ratio = $plannedQty / $yieldQty;

                $run = ProductionRun::query()->create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $recipe->product_id,
                    'outlet_id' => 1,
                    'planned_quantity' => $plannedQty,
                    'yield_quantity' => $yieldQty,
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $request->user()->id,
                    'meta' => [
                        'yield_unit' => $recipe->yield_unit,
                    ],
                ]);

                foreach ($recipe->items as $item) {
                    $ingredientQty = round((float) $item->quantity_required * $ratio, 4);

                    if ($ingredientQty <= 0) {
                        continue;
                    }

                    $inventory->move(
                        (int) $item->ingredient_product_id,
                        1,
                        -abs($ingredientQty),
                        'PRODUCTION_OUT',
                        ProductionRun::class,
                        $run->id,
                        $request->user()->id,
                        $item->ingredient?->cost_price ? (float) $item->ingredient->cost_price : null,
                        'Production issue for ' . ($recipe->product->name ?? 'recipe output')
                    );
                }

                $inventory->move(
                    (int) $recipe->product_id,
                    1,
                    $plannedQty,
                    'PRODUCTION_IN',
                    ProductionRun::class,
                    $run->id,
                    $request->user()->id,
                    $recipe->product?->cost_price ? (float) $recipe->product->cost_price : null,
                    'Production output for ' . ($recipe->product->name ?? 'recipe output')
                );
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['production' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Production run posted.');
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

    public function reservation(Request $request)
    {
        $data = $request->validate([
            'restaurant_table_id' => ['nullable', 'exists:restaurant_tables,id'],
            'customer_name' => ['required', 'max:120'],
            'customer_phone' => ['nullable', 'max:80'],
            'covers' => ['required', 'integer', 'min:1', 'max:50'],
            'reserved_for' => ['required', 'date'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        Reservation::query()->create([
            ...$data,
            'status' => 'booked',
            'created_by' => $request->user()->id,
        ]);

        if (! empty($data['restaurant_table_id'])) {
            RestaurantTable::query()->whereKey($data['restaurant_table_id'])->update(['status' => 'reserved']);
        }

        return back()->with('status', 'Reservation saved.');
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

    public function supplier(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'phone' => ['nullable', 'max:80'],
            'email' => ['nullable', 'email', 'max:120'],
        ]);

        Supplier::query()->updateOrCreate(
            ['name' => trim($data['name'])],
            [
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
            ]
        );

        return back()->with('status', 'Supplier saved.');
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

    public function stockAdjustment(Request $request, InventoryService $inventory)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'counted_qty' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'in:cycle_count,opening_balance,damage,variance,transfer_in,transfer_out,production_correction'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($data, $request, $inventory) {
                $product = Product::query()
                    ->withSum('stockLevels as stock_qty', 'quantity')
                    ->findOrFail((int) $data['product_id']);

                $expectedQty = (float) ($product->stock_qty ?? 0);
                $countedQty = (float) $data['counted_qty'];
                $varianceQty = round($countedQty - $expectedQty, 4);

                if (abs($varianceQty) < 0.0001) {
                    throw new \RuntimeException('Count matches current stock. No adjustment was needed.');
                }

                $adjustment = InventoryAdjustment::query()->create([
                    'product_id' => $product->id,
                    'outlet_id' => 1,
                    'expected_qty' => $expectedQty,
                    'counted_qty' => $countedQty,
                    'variance_qty' => $varianceQty,
                    'unit_cost' => $product->cost_price,
                    'reason' => $data['reason'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $request->user()->id,
                    'meta' => [
                        'source' => 'inventory_module',
                    ],
                ]);

                $inventory->move(
                    (int) $product->id,
                    1,
                    $varianceQty,
                    'ADJUSTMENT',
                    InventoryAdjustment::class,
                    $adjustment->id,
                    $request->user()->id,
                    $product->cost_price ? (float) $product->cost_price : null,
                    trim(($data['notes'] ?? '') . ' | Count reason: ' . str_replace('_', ' ', $data['reason']))
                );
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['stock_adjustment' => $e->getMessage()])->withInput();
        }

        return back()->with('status', 'Stock adjustment posted.');
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

    public function creditPayment(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'in:cash,mpesa,card,bank'],
            'reference' => ['nullable', 'max:120'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        $customer = Customer::query()
            ->withSum('creditAccounts as credit_balance', 'amount')
            ->findOrFail((int) $data['customer_id']);
        $balance = max(0, (float) ($customer->credit_balance ?? 0));
        $amount = round((float) $data['amount'], 2);

        if ($amount > $balance) {
            return back()->withErrors(['credit_payment' => 'Collection amount is greater than the customer balance.'])->withInput();
        }

        CreditAccount::query()->create([
            'customer_id' => $customer->id,
            'sale_id' => null,
            'amount' => -abs($amount),
            'type' => 'credit',
            'due_date' => now()->toDateString(),
            'notes' => trim(($data['notes'] ?? '') . ' | Collection via ' . strtoupper($data['payment_method']) . ($data['reference'] ? ' ref ' . $data['reference'] : '')),
        ]);

        return back()->with('status', 'Credit collection posted.');
    }

    public function user(Request $request)
    {
        User::query()->create([...$request->validate(['name' => ['required'], 'email' => ['required', 'email', 'unique:users,email'], 'role' => ['required', 'in:admin,manager,cashier,waiter,kitchen,inventory'], 'password' => ['required']]), 'password' => Hash::make($request->input('password')), 'is_active' => true]);
        return back()->with('status', 'User saved.');
    }

    public function userStatus(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        abort_if((int) $data['user_id'] === (int) $request->user()->id && ! $request->boolean('is_active'), 422, 'You cannot deactivate your own account.');

        User::query()->whereKey($data['user_id'])->update(['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'User status updated.');
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

    public function openShift(Request $request)
    {
        $data = $request->validate([
            'opening_float' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        $existing = Shift::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'open')
            ->exists();

        if ($existing) {
            return back()->withErrors(['shift' => 'This cashier already has an open shift.']);
        }

        Shift::query()->create([
            'shift_number' => Numbers::next('SFT', 'shifts', 'shift_number'),
            'user_id' => $request->user()->id,
            'opened_by' => $request->user()->id,
            'status' => 'open',
            'opening_float' => (float) $data['opening_float'],
            'expected_cash' => (float) $data['opening_float'],
            'opened_at' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('status', 'Shift opened.');
    }

    public function shiftCashEntry(Request $request)
    {
        $data = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'entry_type' => ['required', 'in:cash_in,cash_out,petty_cash,payout,float_topup'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'max:120'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        $shift = Shift::query()->whereKey($data['shift_id'])->where('status', 'open')->firstOrFail();
        if ($request->user()->role === 'cashier' && (int) $shift->user_id !== (int) $request->user()->id) {
            abort(403, 'You can only post entries to your own shift.');
        }

        ShiftCashEntry::query()->create([
            'shift_id' => $shift->id,
            'entry_type' => $data['entry_type'],
            'amount' => (float) $data['amount'],
            'reason' => $data['reason'],
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $sign = in_array($data['entry_type'], ['cash_out', 'petty_cash', 'payout'], true) ? -1 : 1;
        $shift->update([
            'expected_cash' => round((float) $shift->expected_cash + ($sign * (float) $data['amount']), 2),
        ]);

        return back()->with('status', 'Shift cash entry posted.');
    }

    public function closeShift(Request $request)
    {
        $data = $request->validate([
            'shift_id' => ['required', 'exists:shifts,id'],
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'max:1000'],
        ]);

        $shift = Shift::query()->whereKey($data['shift_id'])->where('status', 'open')->firstOrFail();
        if ($request->user()->role === 'cashier' && (int) $shift->user_id !== (int) $request->user()->id) {
            abort(403, 'You can only close your own shift.');
        }

        $countedCash = (float) $data['counted_cash'];
        $expected = (float) $shift->expected_cash;
        $variance = round($countedCash - $expected, 2);

        $shift->update([
            'status' => 'closed',
            'counted_cash' => $countedCash,
            'variance_amount' => $variance,
            'closed_by' => $request->user()->id,
            'closed_at' => now(),
            'notes' => trim(($shift->notes ? $shift->notes . "\n" : '') . ($data['notes'] ?? '')),
        ]);

        return back()->with('status', 'Shift closed.');
    }
}
