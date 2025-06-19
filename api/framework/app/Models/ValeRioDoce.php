<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ValeRioDoce extends Model
{
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'vale_rio_doce';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
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
		'sinopse',
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

	/**
	 * Get media
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function media()
	{
		return $this->morphMany(Media::class, 'media');
	}
}