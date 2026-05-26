<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'credit_limit'];

    protected $casts = ['credit_limit' => 'decimal:2'];

    public function creditAccounts(): HasMany
    {
        return $this->hasMany(CreditAccount::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
