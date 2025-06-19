<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\AboutAppResource;
use App\Http\Resources\Customer\AboutCompanyResource;
use App\Http\Resources\Customer\GrupoAizResource;
use App\Http\Resources\Customer\PolicyCookiesResource;
use App\Http\Resources\Customer\PrivacyPolicyResource;
use App\Http\Resources\Customer\TermOfUseResource;
use App\Models\AboutApp;
use App\Models\AboutCompany;
use App\Models\GrupoAiz;
use App\Models\PolicyCookies;
use App\Models\PrivacyPolicy;
use App\Models\TermOfUse;
use Illuminate\Http\Request;

/**
 * Class InstitutionalController
 *
 * @resource Institutional
 * @package  App\Http\Controllers\v1\Customer
 */
class InstitutionalController extends Controller
{
	/**
	 * Privacy policy
	 *
	 * @unauthenticated
	 *
	 * @return PrivacyPolicyResource
	 */
	public function privacyPolicy()
	{
		$privacy_policy = PrivacyPolicy::firstOrFail();

		return new PrivacyPolicyResource($privacy_policy);
	}

	/**
	 * Terms of use
	 *
	 * @unauthenticated
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function termsOfUse()
	{
		$term_of_use = TermOfUse::firstOrFail();

		return new TermOfUseResource($term_of_use);
	}

	/**
	 * Grupo AIZ
	 *
	 * @unauthenticated
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function grupoAiz()
	{
		$grupo_aiz = GrupoAiz::firstOrFail();

		return new GrupoAizResource($grupo_aiz);
	}
}
