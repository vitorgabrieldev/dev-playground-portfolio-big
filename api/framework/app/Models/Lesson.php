<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'course_id', 'title', 'description', 'duration', 'order', 'is_preview', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_preview' => 'boolean',
        'is_active' => 'boolean',
    ];
} 