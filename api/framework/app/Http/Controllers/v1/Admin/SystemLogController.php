<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\SystemLogResource;
use App\Models\SystemLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class SystemLogController
 *
 * @resource System Log
 * @package  App\Http\Controllers\v1\Admin
 */
class SystemLogController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'message',
		'url',
		'created_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'message',
		'url',
	];

	/**
	 * Get all logs
	 *
	 * @permission system-log.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `message` `url` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `message` `url`
	 * @urlParam   level string string static(ALERT) Available values: `ALERT` `CRITICAL` `DEBUG` `EMERGENCY` `ERROR` `INFO` `NOTICE` `WARNING`
	 * @urlParam   method string string static(GET) Available values: `DELETE` `GET` `POST`
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
			'level'        => 'sometimes|in:ALERT,CRITICAL,DEBUG,EMERGENCY,ERROR,INFO,NOTICE,WARNING',
			'method'       => 'sometimes|in:DELETE,GET,POST',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = SystemLog::query();

		// No data to export
		if( !$this->getExport )
		{
			// Select
			$items->select('id', 'uuid', 'user_type', 'user_id', 'message', 'level', 'url', 'method', 'created_at');
		}

		// Load relations
		$items->with(['user']);

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
		$level            = $request->input('level');
		$method           = $request->input('method');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

		// level
		if( $level !== null )
		{
			$items->where('level', '=', $level);
		}

		// method
		if( $method !== null )
		{
			$items->where('method', '=', $method);
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

		return SystemLogResource::collection($items);
	}

	/**
	 * Get log
	 *
	 * @permission system-log.show
	 *
	 * @param SystemLog $log
	 *
	 * @return SystemLogResource
	 */
	public function show(SystemLog $log)
	{
		// Load relations
		$log->load(['user']);

		return new SystemLogResource($log);
	}

	/**
	 * Export logs
	 *
	 * @permission system-log.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `message` `url` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `message` `url`
	 * @urlParam   level string string static(ALERT) Available values: `ALERT` `CRITICAL` `DEBUG` `EMERGENCY` `ERROR` `INFO` `NOTICE` `WARNING`
	 * @urlParam   method string string static(GET) Available values: `DELETE` `GET` `POST`
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
				'name'  => 'UUID do usuário',
				'value' => function ($item) {
					return $item->user->uuid ?? '';
				},
			],
			[
				'name'  => 'Nome do usuário',
				'value' => function ($item) {
					return $item->user->name ?? '';
				},
			],
			[
				'name'  => 'Mensagem',
				'value' => 'message',
			],
			[
				'name'  => 'Level',
				'value' => 'level',
			],
			[
				'name'  => 'IP',
				'value' => 'ip',
			],
			[
				'name'  => 'User agent',
				'value' => 'user_agent',
			],
			[
				'name'  => 'Método',
				'value' => 'method',
			],
			[
				'name'  => 'Url',
				'value' => 'url',
			],
			[
				'name'  => 'Contexto',
				'value' => function ($item) {
					return json_encode($item->context);
				},
			],
			[
				'name'  => 'Criação',
				'type'  => 'datetime',
				'value' => 'created_at',
			],
		];

		return $this->exportData($columns, $items, 'system-logs', 'Exportou registro de erros');
	}
}
