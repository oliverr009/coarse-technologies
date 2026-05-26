<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = ['product_id', 'outlet_id', 'quantity', 'movement_type', 'reference_type', 'reference_id', 'before_stock', 'after_stock', 'unit_cost', 'total_cost', 'notes', 'created_by'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'before_stock' => 'decimal:4',
        'after_stock' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

