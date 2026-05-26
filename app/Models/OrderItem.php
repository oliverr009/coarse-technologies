<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'line_total', 'kitchen_status', 'notes', 'modifier_notes'];

    protected $casts = ['quantity' => 'decimal:4', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
