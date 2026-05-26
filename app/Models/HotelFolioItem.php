<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelFolioItem extends Model
{
    protected $fillable = [
        'hotel_folio_id',
        'item_type',
        'description',
        'amount',
        'reference_type',
        'reference_id',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(HotelFolio::class, 'hotel_folio_id');
    }
}
