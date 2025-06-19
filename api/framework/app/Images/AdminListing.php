<?php

namespace App\Images;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class AdminListing implements FilterInterface
{

	public function applyFilter(Image $image)
	{
		return $image->resize(50, 50, function ($constraint) {
			/** @var \Intervention\Image\Constraint $constraint */
			$constraint->aspectRatio();
			$constraint->upsize();
		});
	}
}