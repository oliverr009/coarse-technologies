<?php

namespace App\Services;

use App\Models\CreditAccount;
use App\Models\Order;
use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(private readonly RecipeService $recipes)
    {
    }

    public function postSale(array $cart, array $options, int $userId): Sale
    {
        return DB::transaction(function () use ($cart, $options, $userId) {
            if (count($cart) === 0) {
                throw new \RuntimeException('Add at least one item before posting a bill.');
            }

            $tableId = $options['restaurant_table_id'] ?? null;
            $customerId = $options['customer_id'] ?? null;
            $orderId = $options['order_id'] ?? null;
            $orderType = $options['order_type'] ?? ($tableId ? 'dine_in' : 'takeaway');
            $discountType = $options['discount_type'] ?? 'fixed';
            $discountValue = max(0, (float) ($options['discount_value'] ?? 0));
            $serviceChargeRate = max(0, (float) ($options['service_charge_rate'] ?? 0));
            $payments = $this->cleanPayments($options['payments'] ?? []);

            $lines = collect($cart)->map(function ($line) {
                $product = Product::query()->findOrFail((int) $line['id']);
                $qty = max(0, (float) ($line['qty'] ?? 0));

                return [
                    'product' => $product,
                    'qty' => $qty,
                    'notes' => trim((string) ($line['notes'] ?? '')),
                    'line_total' => $qty * (float) $product->selling_price,
                ];
            })->filter(fn ($line) => $line['qty'] > 0)->values();

            $subtotal = $lines->sum('line_total');
            $discount = $this->discountAmount($subtotal, $discountType, $discountValue);
            $serviceCharge = max(0, ($subtotal - $discount) * ($serviceChargeRate / 100));
            $taxRate = (float) (Setting::query()->where('key', 'tax_rate')->first()?->value ?? 0);
            $taxable = max(0, $subtotal - $discount + $serviceCharge);
            $tax = $taxable * ($taxRate / 100);
            $total = $taxable + $tax;
            $amountPaid = collect($payments)->sum('amount');
            $balance = max(0, $total - $amountPaid);
            $paymentMethod = $this->primaryPaymentMethod($payments, $balance, (bool) $customerId);

            if ($balance > 0 && $paymentMethod !== 'credit') {
                throw new \RuntimeException('Payment is less than bill total. Use credit or add another payment line.');
            }

            $sale = Sale::query()->create([
                'sale_number' => Numbers::next('SAL', 'sales', 'sale_number'),
                'outlet_id' => 1,
                'order_id' => $orderId,
                'order_type' => $orderType,
                'restaurant_table_id' => $tableId,
                'customer_id' => $customerId,
                'cashier_id' => $userId,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'service_charge_amount' => $serviceCharge,
                'service_charge_rate' => $serviceChargeRate,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'amount_paid' => $amountPaid,
                'balance_due' => $balance,
                'status' => 'completed',
                'notes' => $options['notes'] ?? null,
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                $item = $sale->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $line['qty'],
                    'unit_price' => $product->selling_price,
                    'line_total' => $line['line_total'],
                    'notes' => $line['notes'] ?: null,
                ]);

                $this->recipes->consumeSaleItem($sale, $item, $userId);
            }

            foreach ($payments as $payment) {
                $sale->payments()->create($payment);
            }

            if ($balance > 0 && $customerId) {
                CreditAccount::query()->create([
                    'customer_id' => $customerId,
                    'sale_id' => $sale->id,
                    'amount' => $balance,
                    'type' => 'debit',
                    'due_date' => now()->addDays(14)->toDateString(),
                    'notes' => "Credit sale {$sale->sale_number}",
                ]);
            }

            if ($tableId) {
                RestaurantTable::query()->whereKey($tableId)->update(['status' => 'needs_cleaning']);
            }

            if ($orderId) {
                Order::query()->whereKey($orderId)->update(['status' => 'paid']);
            }

            return $sale->load(['items', 'payments', 'table', 'customer']);
        });
    }

    public function holdOrder(array $cart, array $options, int $userId): Order
    {
        return $this->createOrder($cart, $options, $userId, 'held', null);
    }

    public function sendToKitchen(array $cart, array $options, int $userId): Order
    {
        return $this->createOrder($cart, $options, $userId, 'sent', now());
    }

    private function createOrder(array $cart, array $options, int $userId, string $status, mixed $sentAt): Order
    {
        return DB::transaction(function () use ($cart, $options, $userId, $status, $sentAt) {
            if (count($cart) === 0) {
                throw new \RuntimeException('Add at least one item before saving an order.');
            }

            $tableId = $options['restaurant_table_id'] ?? null;
            $orderType = $options['order_type'] ?? ($tableId ? 'dine_in' : 'takeaway');
            $subtotal = 0;

            $order = Order::query()->create([
                'order_number' => Numbers::next('ORD', 'orders', 'order_number'),
                'order_type' => $orderType,
                'restaurant_table_id' => $tableId,
                'customer_id' => $options['customer_id'] ?? null,
                'waiter_id' => $userId,
                'status' => $status,
                'covers' => max(1, (int) ($options['covers'] ?? 1)),
                'notes' => $options['notes'] ?? null,
                'sent_to_kitchen_at' => $sentAt,
            ]);

            foreach ($cart as $line) {
                $product = Product::query()->findOrFail((int) $line['id']);
                $qty = max(0, (float) ($line['qty'] ?? 0));
                if ($qty <= 0) {
                    continue;
                }
                $lineTotal = $qty * (float) $product->selling_price;
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'line_total' => $lineTotal,
                    'kitchen_status' => $status === 'sent' ? 'pending' : 'held',
                    'notes' => trim((string) ($line['notes'] ?? '')) ?: null,
                ]);
            }

            $order->update(['subtotal' => $subtotal]);

            if ($tableId) {
                RestaurantTable::query()->whereKey($tableId)->update(['status' => 'occupied']);
            }

            return $order->load('items');
        });
    }

    private function cleanPayments(array $payments): array
    {
        return collect($payments)
            ->map(fn ($payment) => [
                'method' => $payment['method'] ?? 'cash',
                'amount' => round(max(0, (float) ($payment['amount'] ?? 0)), 2),
                'reference' => trim((string) ($payment['reference'] ?? '')) ?: null,
            ])
            ->filter(fn ($payment) => $payment['amount'] > 0)
            ->values()
            ->all();
    }

    private function discountAmount(float $subtotal, string $type, float $value): float
    {
        $discount = $type === 'percent' ? $subtotal * min($value, 100) / 100 : $value;

        return round(min($subtotal, max(0, $discount)), 2);
    }

    private function primaryPaymentMethod(array $payments, float $balance, bool $hasCustomer): string
    {
        if ($balance > 0 && $hasCustomer) {
            return 'credit';
        }

        $methods = collect($payments)->pluck('method')->unique()->values();

        return $methods->count() > 1 ? 'split' : (string) ($methods->first() ?? 'cash');
    }
}
