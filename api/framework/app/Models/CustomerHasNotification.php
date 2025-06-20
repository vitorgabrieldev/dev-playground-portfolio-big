<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerHasNotification extends Model
{
    use HasFactory;

    protected $table = 'customer_has_notifications';

    protected $fillable = [
        'customer_id', 'notification_id', 'is_read', 'read_at', 'is_deleted', 'deleted_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_deleted' => 'boolean',
        'read_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
} 