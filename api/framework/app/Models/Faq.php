<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'question', 'answer', 'category_id', 'order', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
} 