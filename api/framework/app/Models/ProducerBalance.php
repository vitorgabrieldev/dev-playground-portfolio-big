<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Customer;

class ProducerBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'producer_id';
    public $incrementing = false;

    protected $fillable = [
        'producer_id', 'balance_available', 'balance_blocked', 'updated_at', 'created_at'
    ];

    protected $casts = [
        'balance_available' => 'float',
        'balance_blocked' => 'float',
    ];

    // RELACIONAMENTOS
    public function producer() { return $this->belongsTo(Customer::class, 'producer_id'); }
} 