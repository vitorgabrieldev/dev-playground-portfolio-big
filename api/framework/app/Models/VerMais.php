<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class VerMais extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'ver_mais';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'is_active' => true,
		'order' => 0
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
        'icone',
		'video',
		'order',
		'capa',
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

	/**
	 * capa sizes
	 *
	 * @return object
	 */
	public function getCapaSizes()
	{
		$return = [];

		$image = $this->getAttributeFromArray('capa');

		if( $image )
		{
			// Remove storage/ from begin
			$image = preg_replace('/^storage\//', '', $image);

			// Get urls
			$return = url_image_cache($image);
		}

		return (object) $return;
	}


	/**
	 * icone sizes
	 *
	 * @return object
	 */
	public function getIconeSizes()
	{
		$return = [];

		$image = $this->getAttributeFromArray('icone');

		if( $image )
		{
			// Remove storage/ from begin
			$image = preg_replace('/^storage\//', '', $image);

			// Get urls
			$return = url_image_cache($image);
		}

		return (object) $return;
	}
}