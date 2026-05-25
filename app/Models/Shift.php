<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'shift_number',
        'user_id',
        'opened_by',
        'closed_by',
        'status',
        'opening_float',
        'expected_cash',
        'counted_cash',
        'variance_amount',
        'opened_at',
        'closed_at',
        'notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'opening_float' => 'float',
            'expected_cash' => 'float',
            'counted_cash' => 'float',
            'variance_amount' => 'float',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function cashEntries(): HasMany
    {
        return $this->hasMany(ShiftCashEntry::class);
    }
}
