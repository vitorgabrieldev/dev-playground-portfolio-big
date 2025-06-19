<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'news';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'is_active' => true,
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
        'sinopse',
		'is_active',
        'data_inicio',
		'data_expiracao',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'data_inicio' => 'datetime',
		'data_expiracao' => 'datetime',
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