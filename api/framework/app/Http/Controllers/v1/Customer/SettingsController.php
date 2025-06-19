<?php

namespace App\Http\Controllers\v1\Customer;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class SettingsController
 *
 * @package  App\Http\Controllers\v1\Customer
 */
class SettingsController extends Controller
{

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $prefix;

	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var array
	 */
	protected $explode_comma = [];

	/**
	 * Get settings
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		$items = [];

		foreach( $this->config as $key )
		{
			if( in_array($key, $this->explode_comma) )
			{
				$items[$key] = explode(',', config($this->prefix . '.' . $key));
			}
			else
			{
                if (str_contains($key, 'show'))
				{
					$items[$key] = (bool) config($this->prefix . '.' . $key);
				}
                elseif (str_contains($key, 'porc') || str_contains($key, 'value') || str_contains($key, 'time'))
				{
					$items[$key] = (float) config($this->prefix . '.' . $key);
				}
                elseif(strpos($key, 'catalogo') !== false ||
                    strpos($key, 'file') !== false ||
                    strpos($key, 'img') !== false ||
                    strpos($key, 'image') !== false )
                {
					if( file_exists(public_path(config($this->prefix . '.' . $key))) && config($this->prefix . '.' . $key) != "")
					{
						$items[$key] = asset(config($this->prefix . '.' . $key));
					}
					else
					{
						$items[$key] = null;
					}
                }
				else
				{
					$items[$key] = config($this->prefix . '.' . $key);
				}
			}
		}

		return response()->json(['data' => $items]);
	}
}
