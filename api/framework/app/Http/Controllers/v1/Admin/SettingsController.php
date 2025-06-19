<?php

namespace App\Http\Controllers\v1\Admin;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class SettingsController
 *
 * @package  App\Http\Controllers\v1\Admin
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
                if (strpos($key, 'time') !== false)
				{
					$items[$key] = (int) config($this->prefix . '.' . $key);
				}
                elseif(strpos($key, 'value') !== false ||
                    strpos($key, 'valor') !== false ||
                    strpos($key, 'perc') !== false ||
                    strpos($key, 'porc') !== false )
                {
                    $items[$key] = (float) config($this->prefix . '.' . $key);
                }
                elseif(strpos($key, 'catalogo') !== false ||
                    strpos($key, 'file') !== false ||
                    strpos($key, 'img') !== false ||
                    strpos($key, 'image') !== false )
                {
                    $items[$key] = config($this->prefix . '.' . $key) != "" ? asset(config($this->prefix . '.' . $key)) : null;
                }
				else
				{
					$items[$key] = config($this->prefix . '.' . $key);
				}
			}
		}

		return response()->json(['data' => $items]);
	}

	/**
	 * Update settings
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$changes_old = [];
		$changes     = [];

		DB::beginTransaction();

		try
		{
			foreach( $this->config as $key )
			{
				if( $request->has($key) )
				{
					$item = Setting::where('key', '=', $this->prefix . '.' . $key)->first();

					if( $item )
					{
						$item_old = clone $item;

						if( in_array($key, $this->explode_comma) )
						{
							$item->val = is_null($request->input($key)) ? '' : implode(',', $request->input($key));
						}
						else
						{
							if($key == 'catalogo' || $key == 'file')
							{
								if( $request->hasFile($key) )
								{
									$file = $request->file($key);
									$path = 'media';
									$name = uuid().'.'.$file->getClientOriginalExtension();
									$path = 'storage/'.$file->storeAs($path, $name);

									$item->val = $path;
								}
							}
							else
							{
								$item->val = is_null($request->input($key)) ? '' : $request->input($key);
							}
						}
						$item->save();

						if( $item->wasChanged() )
						{
							$changes_old[] = $item_old;
							$changes[]     = $item;
						}
					}
					else if( $key == 'delete_catalogo' && $request->input($key) == 1)
					{
						$item = Setting::where('key', '=', $this->prefix . '.catalogo')->first();

						$item->val = null;
						$item->save();
					}
				}
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( count($changes) )
		{
			// Save log
			$this->log(__FUNCTION__, null, 'Editou Configurações - ' . $this->name, $changes, $changes_old);
		}

		// Cache new config
		Artisan::call('config:cache');

		return response(null, 204);
	}
}
