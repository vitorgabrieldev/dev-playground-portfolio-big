<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class GrupoAiz extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'grupo_aiz';

	/**
	 * The model's attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
        'text',
        'file',
        'type',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
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