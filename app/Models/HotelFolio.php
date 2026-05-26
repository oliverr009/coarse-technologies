<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelFolio extends Model
{
    protected $fillable = [
        'hotel_reservation_id',
        'hotel_room_id',
        'guest_name',
        'guest_phone',
        'checked_in_at',
        'expected_checkout_at',
        'checked_out_at',
        'room_rate',
        'room_charges',
        'service_charge',
        'tax_amount',
        'payments',
        'balance',
        'status',
        'notes',
        'created_by',
        'closed_by',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'expected_checkout_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'room_rate' => 'decimal:2',
            'room_charges' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'payments' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(HotelReservation::class, 'hotel_reservation_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HotelFolioItem::class);
    }
}
