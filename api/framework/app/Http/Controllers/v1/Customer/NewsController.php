<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class NewsController
 *
 * @resource Noticias
 * @package  App\Http\Controllers\v1\Customer
 */
class NewsController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'name',
		'data_inicio',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
		'text'
	];

	/**
	 * Get all noticias
	 *
	 * @param Request $request
	 * 
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|data_inicio:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 * @urlParam   data_inicio[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   data_inicio[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'			=> 'sometimes|nullable|integer',
			'page'			=> 'sometimes|nullable|integer',
			'orderBy'		=> 'sometimes|nullable|string',
			'search'		=> 'sometimes|nullable|string',
			'data_inicio'   => 'sometimes|array',
			'data_inicio.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = News::query();
		// only Active
		$items->isActive();

		$items->with(['media']);

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));
		$data_inicio_start = $request->has('data_inicio.0') ? Carbon::parse($request->input('data_inicio.0'))->setTimezone('UTC') : null;
		$data_inicio_end   = $request->has('data_inicio.1') ? Carbon::parse($request->input('data_inicio.1'))->setTimezone('UTC') : null;

		// data_inicio
		if( $data_inicio_start )
		{
			$items->where('data_inicio', '>=', $data_inicio_start);
		}
		if( $data_inicio_end )
		{
			$items->where('data_inicio', '<=', $data_inicio_end);
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

		return NewsResource::collection($items);
	}

	/**
	 * Get noticia
	 *
	 * @param News $new
	 *
	 * @return NewsResource
	 */
	public function show(News $new)
	{
		$new->load(['media']);

		return new NewsResource($new);
	}
}