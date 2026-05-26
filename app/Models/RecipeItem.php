<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeItem extends Model
{
    protected $fillable = ['recipe_id', 'ingredient_product_id', 'quantity_required', 'unit', 'wastage_percent', 'cost_snapshot'];

    protected $casts = [
        'quantity_required' => 'decimal:4',
        'wastage_percent' => 'decimal:3',
        'cost_snapshot' => 'decimal:4',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ingredient_product_id');
    }
}

