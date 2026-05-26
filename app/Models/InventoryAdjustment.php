<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'outlet_id',
        'expected_qty',
        'counted_qty',
        'variance_qty',
        'unit_cost',
        'reason',
        'notes',
        'created_by',
        'approved_by',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'expected_qty' => 'float',
            'counted_qty' => 'float',
            'variance_qty' => 'float',
            'unit_cost' => 'float',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
