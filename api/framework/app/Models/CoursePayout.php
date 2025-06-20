<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoursePayout extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'producer_id', 'amount', 'status', 'payout_method_id', 'payout_method', 'payout_data', 'transaction_id', 'requested_at', 'processed_at', 'completed_at'
    ];

    protected $casts = [
        'payout_data' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
} 