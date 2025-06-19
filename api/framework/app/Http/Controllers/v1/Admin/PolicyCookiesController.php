<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\PolicyCookiesResource;
use App\Models\PolicyCookies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class PolicyCookiesController
 *
 * @resource Policy Cookies
 * @package  App\Http\Controllers\v1\Admin
 */
class PolicyCookiesController extends Controller
{

	/**
	 * Get privacy policy
	 *
	 * @permission privacy-policy.edit
	 *
	 * @return PolicyCookiesResource
	 */
	public function index()
	{
		$policy_cookies = PolicyCookies::firstOrFail();

		return new PolicyCookiesResource($policy_cookies);
	}

	/**
	 * Update privacy policy
	 *
	 * @permission privacy-policy.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  text string nullable|string|max:60000 text HTML allowed
	 *
	 * @return PolicyCookiesResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'name' => 'sometimes|required|string|max:191',
			'text' => 'sometimes|nullable|string|max:60000',
		]);

		$policy_cookies = PolicyCookies::firstOrFail();

		$policy_cookies_old = clone $policy_cookies;

		DB::beginTransaction();

		try
		{
			// Update
			$policy_cookies->fill($request->only(['name', 'text']));
			$policy_cookies->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $policy_cookies->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $policy_cookies_old->name, 'Editou a política de cookies', $policy_cookies, $policy_cookies_old);
		}

		return new PolicyCookiesResource($policy_cookies);
	}
}
