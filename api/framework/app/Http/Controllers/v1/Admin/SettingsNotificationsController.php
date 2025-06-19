<?php

namespace App\Http\Controllers\v1\Admin;

use Illuminate\Http\Request;

/**
 * Class SettingsNotificationsController
 *
 * @resource Settings Notifications
 * @package  App\Http\Controllers\v1\Admin
 */
class SettingsNotificationsController extends SettingsController
{

	/**
	 * @var string
	 */
	protected $name = 'Notificações';

	/**
	 * @var string
	 */
	protected $prefix = 'notifications';

	/**
	 * @var array
	 */
	protected $config = [
		'delete_account',
		'create_account',
	];

	/**
	 * @var array
	 */
	protected $explode_comma = [
		'delete_account',
		'create_account',
	];

	/**
	 * Get settings
	 *
	 * @permission settings-notifications.list
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
	 * @permission settings-notifications.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  delete_account[] array array|min:1 static()
	 * @bodyParam  delete_account[0] string required|email email
	 *
	 * @bodyParam  create_account[] array array|min:1 static()
	 * @bodyParam  create_account[0] string required|email email
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'delete_account'   => 'sometimes|required|array|min:1',
			'delete_account.*' => 'required|email',

			'create_account'   => 'sometimes|required|array|min:1',
			'create_account.*' => 'required|email',
		]);

		return parent::update($request);
	}
}
