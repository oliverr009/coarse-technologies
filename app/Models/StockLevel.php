<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    protected $fillable = ['product_id', 'outlet_id', 'quantity'];

    protected $casts = ['quantity' => 'decimal:4'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

