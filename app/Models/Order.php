<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = ['order_number', 'order_type', 'restaurant_table_id', 'customer_id', 'waiter_id', 'status', 'covers', 'subtotal', 'notes', 'sent_to_kitchen_at'];

    protected $casts = ['sent_to_kitchen_at' => 'datetime', 'subtotal' => 'decimal:2'];

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
