<?php

namespace App\Images;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class Preloader implements FilterInterface
{

	public function applyFilter(Image $image)
	{
		return $image->resize(40, 40, function ($constraint) {
			/** @var \Intervention\Image\Constraint $constraint */
			$constraint->aspectRatio();
			$constraint->upsize();
		})->blur(20);
	}
}