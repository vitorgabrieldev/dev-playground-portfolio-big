<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PrivacyPolicy extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'privacy_policy';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'name',
		'text',
	];
}
