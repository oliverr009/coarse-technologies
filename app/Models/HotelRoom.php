<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoom extends Model
{
    protected $fillable = [
        'room_type_id',
        'room_number',
        'floor',
        'status',
        'housekeeping_status',
        'active_guest_name',
        'active_folio_balance',
        'current_rate',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'active_folio_balance' => 'decimal:2',
            'current_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(HotelRoomType::class, 'room_type_id');
    }
}
