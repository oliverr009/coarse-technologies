<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditAccount extends Model
{
    protected $fillable = ['customer_id', 'sale_id', 'amount', 'type', 'due_date', 'notes'];

    protected $casts = ['amount' => 'decimal:2', 'due_date' => 'date'];
}

