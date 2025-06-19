<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class EmailVerification extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'email_verifications';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'token',
		'customer_id',
	];

	/**
	 * Get customer
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function customer()
	{
		return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
	}
}
