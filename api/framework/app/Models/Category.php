<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Course;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    protected $fillable = [
        'uuid', 'parent_id', 'name', 'slug', 'description', 'icon', 'color', 'order', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'parent_id' => 'integer',
        'is_active' => 'boolean',
    ];
} 