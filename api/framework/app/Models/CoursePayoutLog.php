<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoursePayoutLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'producer_id', 'type', 'amount', 'payout_method_id', 'description', 'related_id', 'related_type', 'logged_at'
    ];

    protected $casts = [
        'amount' => 'float',
        'logged_at' => 'datetime',
    ];
} 