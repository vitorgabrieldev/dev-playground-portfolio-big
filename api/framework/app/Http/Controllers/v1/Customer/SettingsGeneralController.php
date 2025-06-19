<?php

namespace App\Http\Controllers\v1\Customer;

use Illuminate\Http\Request;

/**
 * Class SettingsGeneralController
 *
 * @resource Settings General
 * @package  App\Http\Controllers\v1\Customer
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
}
