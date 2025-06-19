<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chat';

    protected $fillable = [
        'uuid',
        'user_id',
        'user_type',
        'pedidosocorro_id',
        'message',
        'lido',
    ];

    protected $casts = [
        'lido' => 'boolean',
    ];

    public function pedidoSocorro()
    {
        return $this->belongsTo(PedidoSocorro::class, 'pedidosocorro_id');
    }

    public function user()
    {
        return $this->morphTo();
    }
}
