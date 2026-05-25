<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftCashEntry extends Model
{
    protected $fillable = [
        'shift_id',
        'entry_type',
        'amount',
        'reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
