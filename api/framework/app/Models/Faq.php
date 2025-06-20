<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\FaqCategory;
use App\Models\User;

class Faq extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'question', 'answer', 'category_id', 'order', 'is_active', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // RELACIONAMENTOS
    public function category() { return $this->belongsTo(FaqCategory::class, 'category_id'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function updatedBy() { return $this->belongsTo(User::class, 'updated_by'); }
} 