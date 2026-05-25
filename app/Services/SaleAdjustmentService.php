<?php

namespace App\Services;

use App\Models\CreditAccount;
use App\Models\Sale;
use App\Models\SaleAdjustment;
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

            $sale->update([
                'status' => 'voided',
                'balance_due' => 0,
            ]);

            return $adjustment;
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
                ->where('adjustment_type', 'refund_sale')
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
}
