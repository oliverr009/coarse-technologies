<?php

namespace App\Services;

use App\Models\CreditAccount;
use App\Models\Sale;
use App\Models\SaleAdjustment;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SaleAdjustmentService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly PosApprovalService $approvals,
    ) {
    }

    public function voidSale(Sale $sale, array $payload, int $actorUserId): SaleAdjustment
    {
        return DB::transaction(function () use ($sale, $payload, $actorUserId) {
            if ($sale->status === 'voided') {
                throw new \RuntimeException('This sale is already voided.');
            }

            if ($sale->adjustments()->where('adjustment_type', 'void_sale')->exists()) {
                throw new \RuntimeException('This sale already has a void record.');
            }

            $approval = $this->approvals->authorizeManagerAction(
                (string) ($payload['manager_pin'] ?? ''),
                (string) ($payload['reason'] ?? '')
            );

            $this->reverseInventory($sale, $actorUserId, 'REVERSAL', 'Sale void reversal');

            if ($sale->customer_id && $sale->balance_due > 0) {
                CreditAccount::query()->create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'amount' => -abs((float) $sale->balance_due),
                    'type' => 'credit',
                    'due_date' => now()->toDateString(),
                    'notes' => "Credit reversal for voided sale {$sale->sale_number}",
                ]);
            }

            $adjustment = $sale->adjustments()->create([
                'adjustment_type' => 'void_sale',
                'status' => 'approved',
                'amount' => (float) $sale->total_amount,
                'reason' => (string) $payload['reason'],
                'notes' => $payload['notes'] ?? null,
                'actor_user_id' => $actorUserId,
                'approver_user_id' => $approval['approver_id'] ?? null,
                'meta' => [
                    'approval_source' => $approval['matched'] ?? null,
                    'payment_method' => $sale->payment_method,
                    'restocked' => true,
                ],
            ]);

            foreach ($sale->items as $saleItem) {
                $adjustment->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product_name,
                    'quantity' => $saleItem->quantity,
                    'unit_price' => $saleItem->unit_price,
                    'line_total' => $saleItem->line_total,
                    'restocked' => true,
                    'notes' => $saleItem->notes,
                    'meta' => ['source' => 'void_sale'],
                ]);
            }

            $sale->update([
                'status' => 'voided',
                'balance_due' => 0,
            ]);

            return $adjustment->load('items');
        });
    }

    public function refundSale(Sale $sale, array $payload, int $actorUserId): SaleAdjustment
    {
        return DB::transaction(function () use ($sale, $payload, $actorUserId) {
            if ($sale->status === 'voided') {
                throw new \RuntimeException('A voided sale cannot be refunded.');
            }

            $approval = $this->approvals->authorizeManagerAction(
                (string) ($payload['manager_pin'] ?? ''),
                (string) ($payload['reason'] ?? '')
            );

            $amount = round(max(0, (float) ($payload['amount'] ?? 0)), 2);
            if ($amount <= 0) {
                throw new \RuntimeException('Refund amount must be greater than zero.');
            }

            $alreadyRefunded = (float) $sale->adjustments()
                ->whereIn('adjustment_type', ['refund_sale', 'return_items'])
                ->sum('amount');

            $remaining = max(0, (float) $sale->total_amount - $alreadyRefunded);
            if ($amount > $remaining) {
                throw new \RuntimeException('Refund amount is greater than the remaining refundable balance.');
            }

            if ($sale->customer_id && $sale->balance_due > 0) {
                CreditAccount::query()->create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'amount' => -min((float) $sale->balance_due, $amount),
                    'type' => 'credit',
                    'due_date' => now()->toDateString(),
                    'notes' => "Credit adjustment for refunded sale {$sale->sale_number}",
                ]);
            }

            $adjustment = $sale->adjustments()->create([
                'adjustment_type' => 'refund_sale',
                'status' => 'approved',
                'amount' => $amount,
                'reason' => (string) $payload['reason'],
                'notes' => $payload['notes'] ?? null,
                'actor_user_id' => $actorUserId,
                'approver_user_id' => $approval['approver_id'] ?? null,
                'meta' => [
                    'approval_source' => $approval['matched'] ?? null,
                    'restocked' => false,
                    'refund_method' => $payload['refund_method'] ?? 'cash',
                ],
            ]);

            $totalRefunded = $alreadyRefunded + $amount;
            $sale->update([
                'status' => $totalRefunded >= (float) $sale->total_amount ? 'refunded' : 'partially_refunded',
            ]);

            return $adjustment;
        });
    }

    public function returnItems(Sale $sale, array $payload, int $actorUserId): SaleAdjustment
    {
        return DB::transaction(function () use ($sale, $payload, $actorUserId) {
            if ($sale->status === 'voided') {
                throw new \RuntimeException('A voided sale cannot accept item returns.');
            }

            $approval = $this->approvals->authorizeManagerAction(
                (string) ($payload['manager_pin'] ?? ''),
                (string) ($payload['reason'] ?? '')
            );

            $rawLines = collect($payload['lines'] ?? [])->filter(fn ($line) => (float) ($line['qty'] ?? 0) > 0)->values();
            if ($rawLines->isEmpty()) {
                throw new \RuntimeException('Choose at least one item quantity to return.');
            }

            $sale->loadMissing('items', 'adjustments.items');

            $returnLines = $rawLines->map(function ($line) use ($sale) {
                $saleItem = $sale->items->firstWhere('id', (int) ($line['sale_item_id'] ?? 0));
                if (! $saleItem) {
                    throw new \RuntimeException('One of the selected return items is missing from this sale.');
                }

                $previouslyReturned = (float) $sale->adjustments
                    ->where('adjustment_type', 'return_items')
                    ->flatMap->items
                    ->where('sale_item_id', $saleItem->id)
                    ->sum('quantity');

                $requestedQty = max(0, (float) ($line['qty'] ?? 0));
                $availableQty = max(0, (float) $saleItem->quantity - $previouslyReturned);

                if ($requestedQty <= 0 || $requestedQty > $availableQty) {
                    throw new \RuntimeException("Return quantity for {$saleItem->product_name} is greater than the remaining sold quantity.");
                }

                $unitPrice = (float) $saleItem->unit_price;
                $lineTotal = round($requestedQty * $unitPrice, 2);

                return [
                    'sale_item' => $saleItem,
                    'qty' => $requestedQty,
                    'restock' => filter_var($line['restock'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'notes' => trim((string) ($line['notes'] ?? '')) ?: null,
                    'line_total' => $lineTotal,
                    'unit_price' => $unitPrice,
                ];
            });

            $amount = round($returnLines->sum('line_total'), 2);
            if ($amount <= 0) {
                throw new \RuntimeException('The selected return lines do not produce a valid refund amount.');
            }

            $alreadyRefunded = (float) $sale->adjustments()
                ->whereIn('adjustment_type', ['refund_sale', 'return_items'])
                ->sum('amount');
            $remaining = max(0, (float) $sale->total_amount - $alreadyRefunded);
            if ($amount > $remaining) {
                throw new \RuntimeException('Return amount is greater than the remaining refundable balance.');
            }

            $adjustment = $sale->adjustments()->create([
                'adjustment_type' => 'return_items',
                'status' => 'approved',
                'amount' => $amount,
                'reason' => (string) $payload['reason'],
                'notes' => $payload['notes'] ?? null,
                'actor_user_id' => $actorUserId,
                'approver_user_id' => $approval['approver_id'] ?? null,
                'meta' => [
                    'approval_source' => $approval['matched'] ?? null,
                    'refund_method' => $payload['refund_method'] ?? 'cash',
                ],
            ]);

            foreach ($returnLines as $returnLine) {
                $saleItem = $returnLine['sale_item'];
                $adjustment->items()->create([
                    'sale_item_id' => $saleItem->id,
                    'product_id' => $saleItem->product_id,
                    'product_name' => $saleItem->product_name,
                    'quantity' => $returnLine['qty'],
                    'unit_price' => $returnLine['unit_price'],
                    'line_total' => $returnLine['line_total'],
                    'restocked' => $returnLine['restock'],
                    'notes' => $returnLine['notes'],
                    'meta' => [
                        'sold_quantity' => (float) $saleItem->quantity,
                    ],
                ]);

                if ($returnLine['restock']) {
                    $this->reverseSaleItemInventory($sale, $saleItem, $returnLine['qty'], $actorUserId, 'REVERSAL', 'Partial item return restock');
                }
            }

            if ($sale->customer_id && $sale->balance_due > 0) {
                CreditAccount::query()->create([
                    'customer_id' => $sale->customer_id,
                    'sale_id' => $sale->id,
                    'amount' => -min((float) $sale->balance_due, $amount),
                    'type' => 'credit',
                    'due_date' => now()->toDateString(),
                    'notes' => "Credit adjustment for returned items on sale {$sale->sale_number}",
                ]);
            }

            $totalRefunded = $alreadyRefunded + $amount;
            $sale->update([
                'status' => $totalRefunded >= (float) $sale->total_amount ? 'refunded' : 'partially_refunded',
            ]);

            return $adjustment->load('items');
        });
    }

    private function reverseInventory(Sale $sale, int $actorUserId, string $movementType, string $notePrefix): void
    {
        $sale->loadMissing('items');

        foreach ($sale->items as $saleItem) {
            $movements = StockMovement::query()
                ->where('movement_type', 'SALE_CONSUMPTION')
                ->where('reference_type', \App\Models\SaleItem::class)
                ->where('reference_id', $saleItem->id)
                ->get();

            foreach ($movements as $movement) {
                $this->inventory->move(
                    (int) $movement->product_id,
                    (int) $movement->outlet_id,
                    abs((float) $movement->quantity),
                    $movementType,
                    \App\Models\Sale::class,
                    $sale->id,
                    $actorUserId,
                    $movement->unit_cost ? (float) $movement->unit_cost : null,
                    "{$notePrefix} {$sale->sale_number}"
                );
            }
        }
    }

    private function reverseSaleItemInventory(Sale $sale, SaleItem $saleItem, float $returnQty, int $actorUserId, string $movementType, string $notePrefix): void
    {
        $originalQty = max(0.0001, (float) $saleItem->quantity);
        $ratio = $returnQty / $originalQty;

        $movements = StockMovement::query()
            ->where('movement_type', 'SALE_CONSUMPTION')
            ->where('reference_type', \App\Models\SaleItem::class)
            ->where('reference_id', $saleItem->id)
            ->get();

        foreach ($movements as $movement) {
            $restoreQty = round(abs((float) $movement->quantity) * $ratio, 4);
            if ($restoreQty <= 0) {
                continue;
            }

            $this->inventory->move(
                (int) $movement->product_id,
                (int) $movement->outlet_id,
                $restoreQty,
                $movementType,
                \App\Models\SaleItem::class,
                $saleItem->id,
                $actorUserId,
                $movement->unit_cost ? (float) $movement->unit_cost : null,
                "{$notePrefix} {$sale->sale_number} · {$saleItem->product_name}"
            );
        }
    }
}
