<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\GeneralException;
use App\Http\Resources\Admin\PushCityResource;
use App\Models\City;
use App\Models\PushCity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

/**
 * Class PushCityController
 *
 * @resource Push City
 * @package  App\Http\Controllers\v1\Admin
 */
class PushCityController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'title',
		'body',
		'scheduled_at',
		'send_at',
		'created_at',
		'city.name',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'title',
		'body',
		'city.name',
	];

	/**
	 * Column map
	 *
	 * @param $column
	 *
	 * @return string
	 */
	protected function columnMap($column)
	{
		$table     = (new PushCity)->getTable();
		$tableCity = (new City)->getTable();

		$columnMap = [
			'city.name' => $tableCity . '.name',
		];

		return $columnMap[$column] ?? $table . '.' . $column;
	}

	/**
	 * Get all push
	 *
	 * @permission push-city.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
     * @urlParam   city_id string string static(uuid)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `city.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `city.name`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'        => 'sometimes|nullable|integer',
			'page'         => 'sometimes|nullable|integer',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
            'city_id'     => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$table     = (new PushCity)->getTable();
		$tableCity = (new City)->getTable();

		$items = PushCity::query();

		// Select
		$items->select($table . '.*');

		// Load relations
		$items->with(['city.state']);

		// City join
		$items->join($tableCity, $table . '.city_id', '=', $tableCity . '.id');

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
        $city            = $request->input('city_id');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

		if( $city !== null )
		{
            $cityid = City::where('uuid',$city)->first(['id']);
			$items->where('city_id', $cityid->id);
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
					$query->orWhere($this->columnMap($searchField), 'like', '%' . $search . '%');
				}
			});
		}

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($this->columnMap($orderByField['name']), $orderByField['sort']);
		}

		// Only get data to export
		if( $this->getExport )
		{
			return $items->get();
		}

		$items = $items->paginate($limit)->appends($request->except('page'));

		return PushCityResource::collection($items);
	}

	/**
	 * Create push
	 *
	 * @permission push-city.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  city_id string required|string static(2) `City` relationship
	 * @bodyParam  title string required|string|max:50 text(50)
	 * @bodyParam  body string required|string|max:100 text(100)
	 * @bodyParam  url string nullable|string|max:191 url
	 * @bodyParam  scheduled_at string nullable|string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Future date
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'city_id'      => 'required|string|exists:' . (new City)->getTable() . ',uuid',
			'title'        => 'required|string|max:50',
			'body'         => 'required|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

        $city = $request->input('city_id');

		// Fill
		$push               = new PushCity($request->only([ 'title', 'body', 'url']));
		$push->uuid         = uuid();
		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;

		if( $city !== null )
		{
            $cityid = City::where('uuid',$city)->first(['id']);
            $push->city_id = $cityid->id;
		}

        // Save
		$push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Criou um novo push por cidade', $push);

		return (new PushCityResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Update push
	 *
	 * @permission push-city.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  uuid integer required|string static(uuid)
	 * @bodyParam  city_id string nullable|string static(2) `City` relationship
	 * @bodyParam  title string nullable|string|max:50 text(50)
	 * @bodyParam  body string nullable|string|max:100 text(100)
	 * @bodyParam  url string nullable|string|max:191 url
	 * @bodyParam  scheduled_at string nullable|string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Future date
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request)
	{
		$this->validate($request, [
			'uuid'        => 'required|string',
			'city_id'      => 'nullable|string|exists:' . (new City)->getTable() . ',uuid',
			'title'        => 'nullable|string|max:50',
			'body'         => 'nullable|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

        $city = $request->input('city_id');

        // Fill
		$push	= (new PushCity())->where("uuid", $request->input("uuid"))->firstOrFail();
		$push_old = clone $push;

		$push->fill($request->only(['city_id', 'title', 'body', 'url']));

		if( $city !== null )
		{
            $cityid = City::where('uuid',$city)->first(['id']);
            $push->city_id = $cityid->id;
		}

		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;
		$push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Editou um push por cidade', $push, $push_old);

		return (new PushCityResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Get push
	 *
	 * @permission push-city.show
	 *
	 * @param PushCity $push
	 *
	 * @return PushCityResource
	 */
	public function show(PushCity $push)
	{
		// Load relations
		$push->load(['city.state']);

		return new PushCityResource($push);
	}

	/**
	 * Remove push
	 *
	 * @permission push-city.delete
	 *
	 * @param PushCity $push
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(PushCity $push)
	{
		if( $push->send_at )
		{
			throw GeneralException::make('push_cant_delete_past_date');
		}

		// Delete
		$push->delete();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Deletou um push por cidade', $push);

		return response(null, 204);
	}

	/**
	 * Export push
	 *
	 * @permission push-city.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `city.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `city.name`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @response {
	 * "file_url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function export(Request $request)
	{
		$this->getExport = true;

		$items = $this->index($request);

		$columns = [
			[
				'name'  => 'UUID',
				'value' => 'uuid',
			],
			[
				'name'  => 'Cidade',
				'value' => function ($item) {
					return $item->city ? $item->city->name . '-' . $item->city->state->abbr : null;
				},
			],
			[
				'name'  => 'Título',
				'value' => 'title',
			],
			[
				'name'  => 'Mensagem',
				'value' => 'body',
			],
			[
				'name'  => 'Url',
				'value' => 'url',
			],
			[
				'name'  => 'Total de usuários',
				'value' => 'total_users',
			],
			[
				'name'  => 'Agendado para',
				'value' => 'scheduled_at',
				'type'  => 'datetime',
			],
			[
				'name'  => 'Enviado em',
				'value' => 'send_at',
				'type'  => 'datetime',
			],
			[
				'name'  => 'Criação',
				'type'  => 'datetime',
				'value' => 'created_at',
			],
		];

		return $this->exportData($columns, $items, 'push-city', 'Exportou push por cidade');
	}
}
