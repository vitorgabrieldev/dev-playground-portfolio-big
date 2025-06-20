<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Category;
use App\Models\Lesson;

class Course extends Model
{
    use HasFactory, SoftDeletes;


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    protected $fillable = [
        'uuid', 'creator_id', 'category_id', 'title', 'description', 'short_description', 'price',
        'preview_content', 'status', 'rejection_reason', 'approved_by', 'approved_at', 'total_duration',
        'total_sales', 'total_revenue', 'rating', 'total_ratings', 'tags', 'requirements', 'objectives',
        'level', 'languages', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'tags' => 'array',
        'requirements' => 'array',
        'objectives' => 'array',
        'languages' => 'array',
        'approved_at' => 'datetime',
    ];
} 