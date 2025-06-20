<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class CustomerPayoutMethod extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'label', 'bank_name', 'bank_code', 'agency', 'account', 'account_type', 'holder_name', 'holder_document', 'is_default', 'is_active'
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // RELACIONAMENTOS
    public function customer() { return $this->belongsTo(Customer::class); }
} 