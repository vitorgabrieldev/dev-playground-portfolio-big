<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TermOfUse extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'terms_of_use';

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
