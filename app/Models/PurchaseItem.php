<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = ['purchase_id', 'product_id', 'quantity', 'unit_cost', 'line_total'];

    protected $casts = ['quantity' => 'decimal:4', 'unit_cost' => 'decimal:2', 'line_total' => 'decimal:2'];
}

