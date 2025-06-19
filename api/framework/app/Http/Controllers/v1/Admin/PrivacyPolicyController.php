<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\PrivacyPolicyResource;
use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class PrivacyPolicyController
 *
 * @resource Privacy Policy
 * @package  App\Http\Controllers\v1\Admin
 */
class PrivacyPolicyController extends Controller
{

	/**
	 * Get privacy policy
	 *
	 * @permission privacy-policy.edit
	 *
	 * @return PrivacyPolicyResource
	 */
	public function index()
	{
		$privacy_policy = PrivacyPolicy::firstOrFail();

		return new PrivacyPolicyResource($privacy_policy);
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
	 * @return PrivacyPolicyResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'name' => 'sometimes|required|string|max:191',
			'text' => 'sometimes|nullable|string|max:60000',
		]);

		$privacy_policy = PrivacyPolicy::firstOrFail();

		$privacy_policy_old = clone $privacy_policy;

		DB::beginTransaction();

		try
		{
			// Update
			$privacy_policy->fill($request->only(['name', 'text']));
			$privacy_policy->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $privacy_policy->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $privacy_policy_old->name, 'Editou a política de privacidade', $privacy_policy, $privacy_policy_old);
		}

		return new PrivacyPolicyResource($privacy_policy);
	}
}
