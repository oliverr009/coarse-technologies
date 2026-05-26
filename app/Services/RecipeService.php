<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\Sale;
use App\Models\SaleItem;

class RecipeService
{
    public function __construct(
        private readonly InventoryService $inventory,
        private readonly UnitConversionService $units
    ) {
    }

    public function consumeSaleItem(Sale $sale, SaleItem $saleItem, ?int $userId): void
    {
        $product = Product::query()->findOrFail($saleItem->product_id);

        if ($product->product_type === 'resale_item') {
            $this->inventory->move($product->id, $sale->outlet_id, -abs((float) $saleItem->quantity), 'SALE_CONSUMPTION', SaleItem::class, $saleItem->id, $userId, (float) $product->cost_price, 'Direct resale stock deduction');
            return;
        }

        if (! in_array($product->product_type, ['finished_product', 'semi_finished'], true)) {
            return;
        }

        $recipe = Recipe::query()
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->with('items.ingredient')
            ->latest('version')
            ->first();

        if (! $recipe) {
            return;
        }

        $multiplier = (float) $saleItem->quantity / max((float) $recipe->yield_quantity, 0.0001);

        foreach ($recipe->items as $item) {
            $required = ((float) $item->quantity_required * $multiplier) * (1 + ((float) $item->wastage_percent / 100));
            $stockQty = $this->units->convert($required, $item->unit, $item->ingredient->unit);

            $this->inventory->move(
                $item->ingredient_product_id,
                $sale->outlet_id,
                -abs($stockQty),
                'SALE_CONSUMPTION',
                SaleItem::class,
                $saleItem->id,
                $userId,
                $item->cost_snapshot ? (float) $item->cost_snapshot : (float) $item->ingredient->cost_price,
                "Recipe consumption for {$product->name}"
            );
        }
    }
}

