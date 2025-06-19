<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PushGeneral extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'push_general';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'title',
		'body',
		'url',
		'total_users',
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
}
