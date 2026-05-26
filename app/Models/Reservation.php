<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'restaurant_table_id',
        'customer_name',
        'customer_phone',
        'covers',
        'reserved_for',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'reserved_for' => 'datetime',
            'covers' => 'integer',
        ];
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
