<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = ['purchase_number', 'supplier_id', 'outlet_id', 'total_amount', 'status', 'notes', 'created_by'];

    protected $casts = ['total_amount' => 'decimal:2'];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }
}

