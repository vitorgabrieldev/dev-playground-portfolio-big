<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customer;
use App\Models\Notification;

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

    // RELACIONAMENTOS
    public function customer() { return $this->belongsTo(Customer::class); }
    public function notification() { return $this->belongsTo(Notification::class); }
}