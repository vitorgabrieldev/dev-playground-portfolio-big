<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\PerfisAcessoResource;
use App\Models\PerfisAcesso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class PerfisAcessoController
 *
 * @resource Perfis de acesso
 * @package  App\Http\Controllers\v1\Customer
 */
class PerfisAcessoController extends Controller
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
		'text',
	];

	/**
	 * Get all perfis de acesso
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `is_active` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
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
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = PerfisAcesso::query();
		// only Active
		$items->isActive();

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

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

		$items = $items->paginate($limit)->appends($request->except('page'));

		return PerfisAcessoResource::collection($items);
	}

	/**
	 * Get perfis de acesso
	 *
	 * @param PerfisAcesso $perfil
	 *
	 * @return PerfisAcessoResource
	 */
	public function show(PerfisAcesso $perfil)
	{
		return new PerfisAcessoResource($perfil);
	}
}
