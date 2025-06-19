<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Banners extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'banners';

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
		'frase',
		'file',
		'type',
		'link',
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
	 * file sizes
	 *
	 * @return object
	 */
	public function getFileSizes()
	{
		$return = [];

		$image = $this->getAttributeFromArray('file');

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
