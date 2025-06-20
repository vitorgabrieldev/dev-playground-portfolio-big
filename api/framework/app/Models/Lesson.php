<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\User;

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

    // RELACIONAMENTOS
    public function course() { return $this->belongsTo(Course::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }
} 