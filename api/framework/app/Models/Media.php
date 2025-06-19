<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Media extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'media';

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
		'type',
		'file',
		'name',
		'description',
		'mime',
		'size',
		'width',
		'height',
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
	 * File sizes
	 *
	 * @return object
	 */
	public function getFileSizes()
	{
		$return = [];

		$media_type = $this->getAttributeFromArray('media_type');
		$file       = $this->getAttributeFromArray('file');
		$type       = $this->getAttributeFromArray('type');

		if( Str::startsWith($type, 'image') )
		{
			// Remove storage/ from begin
			$image = preg_replace('/^storage\//', '', $file);

			// Get urls
			$return = url_image_cache($image);
		}

		return (object) $return;
	}

	/**
	 * Get item
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphTo
	 */
	public function media()
	{
		return $this->morphTo('media');
	}
}
