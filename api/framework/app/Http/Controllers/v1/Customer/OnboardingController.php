<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Admin\OnboardingResource;
use App\Models\Onboarding;
use Illuminate\Http\Request;

/**
 * Class OnboardingController
 *
 * @resource OnBoarding
 * @package  App\Http\Controllers\v1\Customer
 */
class OnboardingController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
        'frase',
        'frase2',
		'created_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
        'is_active',
        'created_at'
	];

	/**
	 * Get onboarding
	 *
	 * @permission onboarding.show
	 *
	 * @return OnboardingResource
	 */
	public function index()
	{
        $onboarding = Onboarding::firstOrFail();

		return new OnboardingResource($onboarding);
	}

}