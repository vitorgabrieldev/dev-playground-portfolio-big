<?php

namespace App\Http\Resources\Customer;

class MediaResource extends Resource
{

	/**
	 * Complement to transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array
	 */
	public function toArrayComplement($request)
	{
		return [
			'media_type',
			'media_id',
			'type',
			$this->mergeWhen($this->resource->hasAttribute('file'), function () {
				return [
					'file'       => asset($this->file),
					'file_sizes' => $this->getFileSizes(),
				];
			}),
			$this->mergeWhen(($this->resource->hasAttribute('type') and $this->resource->hasAttribute('file') and $this->type === 'video'), function () {
				return [
					'youtube_id'        => $this->file ? youtube_id($this->file) : null,
					'youtube_url_embed' => $this->file ? youtube_url_embed($this->file) : null,
					'youtube_image'     => $this->file ? youtube_image($this->file) : null,
				];
			}),
			'mime',
			'size',
			'description',
			'width',
			'height',
		];
	}
}
