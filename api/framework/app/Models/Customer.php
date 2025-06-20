<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\CoursePurchase;
use App\Models\CustomerPaymentMethod;
use App\Models\CustomerPayoutMethod;
use App\Models\ProducerBalance;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'fullname', 'email', 'password', 'avatar', 'bio', 'document', 'birthdate', 'gender',
        'phone', 'whatsapp', 'website', 'social_links', 'address_street', 'address_number',
        'address_complement', 'address_neighborhood', 'address_city', 'address_state',
        'address_zipcode', 'address_country', 'email_verified_at', 'phone_verified_at',
        'is_active', 'is_blocked', 'blocked_reason', 'last_login_at', 'last_ip', 'register_ip',
        'register_source', 'language', 'notification_preferences', 'referral_code', 'referred_by',
        'custom_data', 'cpf_front_image', 'remember_token'
    ];

    protected $casts = [
        'social_links' => 'array',
        'notification_preferences' => 'array',
        'custom_data' => 'array',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    // RELACIONAMENTOS
    public function coursesCriados() { return $this->hasMany(Course::class, 'creator_id'); }
    public function coursesAprovados() { return $this->hasMany(Course::class, 'approved_by'); }
    public function coursesCriadosPor() { return $this->hasMany(Course::class, 'created_by'); }
    public function coursesAtualizadosPor() { return $this->hasMany(Course::class, 'updated_by'); }
    public function coursePurchases() { return $this->hasMany(CoursePurchase::class); }
    public function customerPaymentMethods() { return $this->hasMany(CustomerPaymentMethod::class); }
    public function customerPayoutMethods() { return $this->hasMany(CustomerPayoutMethod::class); }
    public function producerBalance() { return $this->hasOne(ProducerBalance::class, 'producer_id'); }
} 