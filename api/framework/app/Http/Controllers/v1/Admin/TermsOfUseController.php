<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\TermOfUseResource;
use App\Models\TermOfUse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Class TermsOfUseController
 *
 * @resource Terms Of Use
 * @package  App\Http\Controllers\v1\Admin
 */
class TermsOfUseController extends Controller
{


	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'name',
		'is_active',
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
	 * Get all terms of use
	 *
	 * @permission terms-of-use.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `is_active` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'        => 'sometimes|nullable|integer',
			'page'         => 'sometimes|nullable|integer',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'is_active'    => 'sometimes|integer',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = TermOfUse::query();

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
		$is_active        = $request->input('is_active');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

		// is_active
		if( $is_active !== null )
		{
			$items->where('is_active', '=', $is_active ? 1 : 0);
		}

		// created_at
		if( $created_at_start )
		{
			$items->where('created_at', '>=', $created_at_start);
		}
		if( $created_at_end )
		{
			$items->where('created_at', '<=', $created_at_end);
		}

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

		// Only get data to export
		if( $this->getExport )
		{
			return $items->get();
		}

		$items = $items->paginate($limit)->appends($request->except('page'));

		return TermOfUseResource::collection($items);
	}

	/**
	 * Update terms of use
	 *
	 * @permission terms-of-use.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string nullable|string|max:191 string
	 * @bodyParam  text string nullable|string|max:60000 text HTML allowed
	 *
	 * @return TermOfUseResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'name' => 'sometimes|required|string|max:191',
			'text' => 'sometimes|nullable|string|max:60000',
		]);

		$terms_of_use = TermOfUse::first();
		$terms_of_use_old = clone $terms_of_use;

		DB::beginTransaction();

		try
		{
			// Update
			$terms_of_use->fill($request->only(['name', 'text']));
			$terms_of_use->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $terms_of_use->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $terms_of_use_old->name, 'Editou os termos de uso', $terms_of_use, $terms_of_use_old);
		}

		return new TermOfUseResource($terms_of_use);
	}
}
