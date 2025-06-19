<?php

namespace App\Models;

use Illuminate\Support\Str;

class Log extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'logs';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'log_name',
		'message',
		'action',
		'old_data',
		'new_data',
		'ip',
		'user_agent',
		'url',
		'method',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'old_data' => 'object',
		'new_data' => 'object',
	];

	/**
	 * Set the log name
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public function setLogNameAttribute($value)
	{
		$this->attributes['log_name'] = Str::limit($value, 187, '...');
	}

	/**
	 * Set the message
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public function setMessageAttribute($value)
	{
		$this->attributes['message'] = Str::limit($value, 187, '...');
	}

	/**
	 * Get user owner
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id')->withTrashed();
	}

	/**
	 * Get the item
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function log()
	{
		return $this->morphTo('log');
	}
}
