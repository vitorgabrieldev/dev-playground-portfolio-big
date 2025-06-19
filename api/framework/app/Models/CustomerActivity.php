<?php

namespace App\Models;

use Illuminate\Support\Str;

class CustomerActivity extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'customers_activities';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'customer_id',
		'activity_name',
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
	 * Set the activity name.
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	public function setActivityNameAttribute($value)
	{
		$this->attributes['activity_name'] = Str::limit($value, 187, '...');
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
	 * Get customer
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function customer()
	{
		return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
	}

	/**
	 * Get item
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function activity()
	{
		return $this->morphTo('activity');
	}
}
