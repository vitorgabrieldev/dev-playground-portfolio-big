<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
} 