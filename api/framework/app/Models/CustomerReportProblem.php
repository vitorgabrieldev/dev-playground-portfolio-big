<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerReportProblem extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'customers_report_problem';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'is_answered' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'customer_id',
		'message',
		'answer',
		'is_answered',
		'answered_at',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'is_answered' => 'boolean',
		'answered_at' => 'datetime',
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
