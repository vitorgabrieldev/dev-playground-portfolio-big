<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PushUser extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'push_user';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
        'user_id',
        'user_type',
		'pr',
		'title',
		'body',
		'url',
        'text',
        'max_value',
        'min_value',
        'is_alert',
        'read',
		'api_response',
		'scheduled_at',
		'send_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'scheduled_at' => 'datetime',
		'send_at'      => 'datetime',
		'api_response' => 'array',
	];

	/**
	 * Get the user
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	// public function user()
	// {
	// 	return $this->belongsTo(Customer::class, 'customer_id');
	// }
    public function user()
    {
        return $this->morphTo();
    }
}
