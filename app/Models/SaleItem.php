<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'line_total', 'notes'];

    protected $casts = ['quantity' => 'decimal:4', 'unit_price' => 'decimal:2', 'line_total' => 'decimal:2'];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
