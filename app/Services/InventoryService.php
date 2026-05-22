<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    public function move(
        int $productId,
        int $outletId,
        float $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
        ?float $unitCost = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use ($productId, $outletId, $quantity, $type, $referenceType, $referenceId, $userId, $unitCost, $notes) {
            $stock = StockLevel::query()
                ->where('product_id', $productId)
                ->where('outlet_id', $outletId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                $stock = StockLevel::query()->create([
                    'product_id' => $productId,
                    'outlet_id' => $outletId,
                    'quantity' => 0,
                ])->refresh();
            }

            $before = (float) $stock->quantity;
            $after = $before + $quantity;

            if ($after < 0 && ! $this->allowNegativeInventory()) {
                throw new RuntimeException("Insufficient stock for product #{$productId}. Available {$before}, required " . abs($quantity));
            }

            $stock->update(['quantity' => $after]);

            return StockMovement::query()->create([
                'product_id' => $productId,
                'outlet_id' => $outletId,
                'quantity' => $quantity,
                'movement_type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'before_stock' => $before,
                'after_stock' => $after,
                'unit_cost' => $unitCost,
                'total_cost' => $unitCost === null ? null : abs($quantity) * $unitCost,
                'notes' => $notes,
                'created_by' => $userId,
            ]);
        });
    }

    private function allowNegativeInventory(): bool
    {
        $value = Setting::query()->where('key', 'allow_negative_inventory')->first()?->value ?? false;

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
