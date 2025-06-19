<?php

namespace App\Images;

use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class AppListing implements FilterInterface
{

	public function applyFilter(Image $image)
	{
		return $image->fit(148, 148);
	}
}