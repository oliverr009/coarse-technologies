<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRun extends Model
{
    protected $fillable = [
        'recipe_id',
        'product_id',
        'outlet_id',
        'planned_quantity',
        'yield_quantity',
        'notes',
        'created_by',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'planned_quantity' => 'float',
            'yield_quantity' => 'float',
            'meta' => 'array',
        ];
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
