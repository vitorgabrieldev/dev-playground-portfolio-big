<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\GeneralException;
use App\Http\Resources\Admin\PushStateResource;
use App\Models\PushState;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

/**
 * Class PushStateController
 *
 * @resource Push State
 * @package  App\Http\Controllers\v1\Admin
 */
class PushStateController extends Controller
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
		'state.name',
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
		'state.name',
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
		$table      = (new PushState)->getTable();
		$tableState = (new State)->getTable();

		$columnMap = [
			'state.name' => $tableState . '.name',
		];

		return $columnMap[$column] ?? $table . '.' . $column;
	}

	/**
	 * Get all push
	 *
	 * @permission push-state.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
     * @urlParam   state_id string string static(uuid)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `state.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `state.name`
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
            'state_id'     => 'sometimes|nullable|string',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$table      = (new PushState)->getTable();
		$tableState = (new State)->getTable();

		$items = PushState::query();

		// Select
		$items->select($table . '.*');

		// Load relations
		$items->with(['state']);

		// State join
		$items->join($tableState, $table . '.state_id', '=', $tableState . '.id');

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
        $state            = $request->input('state_id');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

		if( $state !== null )
		{
            $stateid = State::where('uuid',$state)->first(['id']);
			$items->where('state_id', $stateid->id);
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

		return PushStateResource::collection($items);
	}

	/**
	 * Create push
	 *
	 * @permission push-state.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  state_id string required|string static(2) `State` relationship
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
			'state_id'     => 'required|string|exists:' . (new State)->getTable() . ',uuid',
			'title'        => 'required|string|max:50',
			'body'         => 'required|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

		// Fill
		$push               = new PushState($request->only(['title', 'body', 'url']));
		$push->uuid         = uuid();
		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;
        $state            = $request->input('state_id');

		if( $state !== null )
		{
            $stateid = State::where('uuid',$state)->first(['id']);
			$push->state_id = $stateid->id;
		}

        $push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Criou um novo push por estado', $push);

		return (new PushStateResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Update push
	 *
	 * @permission push-state.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  uuid integer required|string static(uuid)
	 * @bodyParam  state_id string nullable|string static(2) `State` relationship
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
			'state_id'     => 'nullable|string|exists:' . (new State)->getTable() . ',uuid',
			'title'        => 'nullable|string|max:50',
			'body'         => 'nullable|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

        $state            = $request->input('state_id');

		// Fill
		$push               = (new PushState())->where("uuid", $request->input("uuid"))->firstOrFail();
		$push_old = clone $push;

		$push->fill($request->only(['title', 'body', 'url']));


		if( $state !== null )
		{
            $stateid = State::where('uuid',$state)->first(['id']);
			$push->state_id = $stateid->id;
		}

		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;
		$push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Editou um push por estado', $push, $push_old);

		return (new PushStateResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Get push
	 *
	 * @permission push-state.show
	 *
	 * @param PushState $push
	 *
	 * @return PushStateResource
	 */
	public function show(PushState $push)
	{
		// Load relations
		$push->load(['state']);

		return new PushStateResource($push);
	}

	/**
	 * Remove push
	 *
	 * @permission push-state.delete
	 *
	 * @param PushState $push
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(PushState $push)
	{
		if( $push->send_at )
		{
			throw GeneralException::make('push_cant_delete_past_date');
		}

		// Delete
		$push->delete();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Deletou um push por estado', $push);

		return response(null, 204);
	}

	/**
	 * Export push
	 *
	 * @permission push-state.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `state.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `state.name`
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
				'name'  => 'Estado',
				'value' => function ($item) {
					return $item->state->name ?? null;
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

		return $this->exportData($columns, $items, 'push-state', 'Exportou push por estado');
	}
}
