<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Onboarding extends Model
{
    use HasFactory;
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'onboarding';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'uuid',
		'frase',
		'frase2',
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
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'deleted_at',
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