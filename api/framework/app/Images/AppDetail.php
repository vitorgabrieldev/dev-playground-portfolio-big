<?php

namespace App\Images;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class AppDetail implements FilterInterface
{

	public function applyFilter(Image $image)
	{
		return $image->fit(500, 500);
	}
}