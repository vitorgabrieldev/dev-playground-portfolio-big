<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AboutApp extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'about_app';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'name',
		'file',
		'file_mobile',
		'text',
	];
}
