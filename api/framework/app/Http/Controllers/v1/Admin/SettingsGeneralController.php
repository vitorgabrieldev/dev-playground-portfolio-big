<?php

namespace App\Http\Controllers\v1\Admin;

use Illuminate\Http\Request;

/**
 * Class SettingsGeneralController
 *
 * @resource Settings General
 * @package  App\Http\Controllers\v1\Admin
 */
class SettingsGeneralController extends SettingsController
{

	/**
	 * @var string
	 */
	protected $name = 'Gerais';

	/**
	 * @var string
	 */
	protected $prefix = 'general';

	/**
	 * @var array
	 */
	protected $config = [
		'app_url_android',
		'app_url_ios',
		'email_support',
		'wpp_geral',
		'wpp_equip',
		'catalogo',
	];

	/**
	 * Get settings
	 *
	 * @permission settings-general.list
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index()
	{
		return parent::index();
	}

	/**
	 * Update settings
	 *
	 * @permission settings-general.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  app_url_android string nullable|string|url static(https://play.google.com/store/apps/details?id=com.facebook.katana)
	 * @bodyParam  app_url_ios string nullable|string|url static(https://apps.apple.com/br/app/facebook/id284882215)
     * @bodyParam  email_support string string|email email
     * @bodyParam  wpp_geral string string|email email
	 * @bodyParam  wpp_equip string sometimes|required|string string
	 * @bodyParam  catalogo file required|mimes:pdf|max:4mb file
	 * @bodyParam  delete_catalogo boolean sometimes|nullable|boolean boolean
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'app_url_android'	=> 'sometimes|nullable|string|url',
			'app_url_ios'		=> 'sometimes|nullable|string|url',
			'email_support'		=> 'sometimes|required|email',
			'wpp_geral'			=> 'sometimes|nullable|string',
			'wpp_equip'			=> 'sometimes|nullable|string',
			'catalogo'			=> 'sometimes|nullable|mimes:pdf|max:4000',
			'delete_catalogo'	=> 'sometimes|nullable|boolean',
		]);

		array_push($this->config, 'delete_catalogo');

		return parent::update($request);
	}
}
