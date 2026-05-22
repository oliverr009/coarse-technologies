<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['category', 'description', 'amount', 'payment_method', 'status', 'created_by'];

    protected $casts = ['amount' => 'decimal:2'];
}

