<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'outlet_id',
        'order_id',
        'order_type',
        'restaurant_table_id',
        'customer_id',
        'cashier_id',
        'payment_method',
        'subtotal',
        'discount_amount',
        'discount_type',
        'discount_value',
        'service_charge_amount',
        'service_charge_rate',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(SaleAdjustment::class);
    }

    public function returnedItemsQuantity(int $saleItemId): float
    {
        return (float) $this->adjustments()
            ->where('adjustment_type', 'return_items')
            ->with('items')
            ->get()
            ->flatMap->items
            ->where('sale_item_id', $saleItemId)
            ->sum('quantity');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
