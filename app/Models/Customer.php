<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'credit_limit'];

    protected $casts = ['credit_limit' => 'decimal:2'];
}

