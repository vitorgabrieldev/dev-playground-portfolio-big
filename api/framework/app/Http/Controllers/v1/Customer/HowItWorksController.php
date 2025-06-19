<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\HowItWorksResource;
use App\Models\HowItWorks;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class HowItWorksController
 *
 * @resource How It Works
 * @package  App\Http\Controllers\v1\Customer
 */
class HowItWorksController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'name',
		'order',
		'created_at',
		'updated_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
	];

	/**
	 * Get all how it works
	 *
	 * @permission how-it-works.list
	 *
	 * @unauthenticated
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'   => 'sometimes|nullable|integer',
			'page'    => 'sometimes|nullable|integer',
			'orderBy' => 'sometimes|nullable|string',
			'search'  => 'sometimes|nullable|string',
		]);

		$items = HowItWorks::query();

		// Only active
		$items->isActive();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));

		if( $search and count($this->fieldSearchable) )
		{
			$items->where(function ($query) use ($search) {
				foreach( $this->fieldSearchable as $searchField )
				{
					$query->orWhere($searchField, 'like', '%' . $search . '%');
				}
			});
		}

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($orderByField['name'], $orderByField['sort']);
		}

		$items = $items->paginate($limit)->appends($request->except('page'));

		return HowItWorksResource::collection($items);
	}

	/**
	 * Get how it works
	 *
	 * @unauthenticated
	 *
	 * @param HowItWorks $how_it_works
	 *
	 * @return HowItWorksResource
	 */
	public function show(HowItWorks $how_it_works)
	{
		if( !$how_it_works->is_active )
		{
			abort(404);
		}

		return new HowItWorksResource($how_it_works);
	}
}
