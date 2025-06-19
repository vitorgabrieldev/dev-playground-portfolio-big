<?php

namespace App\Http\Resources\Admin;

class AboutAppResource extends Resource
{

	/**
	 * Complement to transform the resource into an array.
	 *
	 * @param $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [
			'text',
			'name' => $this->name,
            'file' => asset($this->file),
            'file_mobile' => asset($this->file_mobile),
            'video' => $this->video,
		];
	}
}
