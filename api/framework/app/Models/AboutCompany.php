<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AboutCompany extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'about_company';

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
