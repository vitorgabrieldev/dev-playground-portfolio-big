<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class HowItWorks extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'how_it_works';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'order'     => 0,
		'is_active' => 1,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'name',
		'text',
		'order',
		'is_active',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'is_active' => 'boolean',
	];
}
