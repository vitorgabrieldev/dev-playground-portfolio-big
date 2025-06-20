<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;
use App\Models\Course;
use App\Models\CustomerPaymentMethod;

class CoursePurchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'customer_id', 'course_id', 'price_paid', 'platform_fee', 'creator_revenue', 'payment_status',
        'release_status', 'purchased_at', 'released_at', 'withdrawn_at', 'payment_method_id', 'transaction_id', 'is_active'
    ];

    protected $casts = [
        'purchased_at' => 'datetime',
        'released_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // RELACIONAMENTOS
    public function customer() { return $this->belongsTo(Customer::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function paymentMethod() { return $this->belongsTo(CustomerPaymentMethod::class, 'payment_method_id'); }
} 