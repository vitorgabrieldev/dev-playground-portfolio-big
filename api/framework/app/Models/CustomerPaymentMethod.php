<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerPaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'type', 'label', 'last_digits', 'brand', 'holder_name', 'expiration', 'bank_data', 'is_default', 'is_active'
    ];

    protected $casts = [
        'bank_data' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
} 