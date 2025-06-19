<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class PolicyCookies extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'policy_cookies';

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
