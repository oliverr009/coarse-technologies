<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleAdjustmentItem extends Model
{
    protected $fillable = [
        'sale_adjustment_id',
        'sale_item_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'line_total',
        'restocked',
        'notes',
        'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'restocked' => 'boolean',
        'meta' => 'array',
    ];

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(SaleAdjustment::class, 'sale_adjustment_id');
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }
}
