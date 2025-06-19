<?php

namespace App\Extensions;

use Illuminate\Cache\FileStore;

class CacheFileStore extends FileStore
{

	/**
	 * Create the file cache directory if necessary.
	 *
	 * @param string $path
	 *
	 * @return void
	 */
	protected function ensureCacheDirectoryExists($path)
	{
		if( !$this->files->exists(dirname($path)) )
		{
			$this->files->makeDirectory(dirname($path), 0775, true, true);

			// Fix folder permission
			chmod(dirname($path), 0775);
			chmod(dirname(dirname($path)), 0775);
		}
	}
}
