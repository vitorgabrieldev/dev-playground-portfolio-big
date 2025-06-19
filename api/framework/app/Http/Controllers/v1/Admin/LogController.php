<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\LogResource;
use App\Models\Log;
use Illuminate\Http\Request;

/**
 * Class LogController
 *
 * @resource Log
 * @package  App\Http\Controllers\v1\Admin
 */
class LogController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'user_id',
		'log_type',
		'log_id',
		'log_name',
		'message',
		'action',
		'ip',
		'user_agent',
		'url',
		'method',
		'created_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'user_id',
		'log_type',
		'log_id',
		'log_name',
		'message',
		'action',
		'ip',
		'user_agent',
		'url',
		'method',
	];

	/**
	 * Get all logs
	 *
	 * @permission log.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)image
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `user_id` `log_type` `log_id` `log_name` `message` `action` `ip` `user_agent` `url` `method` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `user_id` `log_type` `log_id` `log_name` `message` `action` `ip` `user_agent` `url` `method`
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'   => 'sometimes|nullable|integer',
			'page'    => 'sometimes|nullable|integer',
			'orderBy' => 'sometimes|nullable|string',
			'search'  => 'sometimes|nullable|string',
		]);

		$items = Log::query();

		// No data to export
		if( !$this->getExport )
		{
			// Select
			$items->select('uuid', 'user_id', 'log_type', 'log_id', 'log_name', 'message', 'action', 'ip', 'user_agent', 'url', 'method', 'created_at');
		}

		// Load relations
		$items->with(['user']);

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

		// Only get data to export
		if( $this->getExport )
		{
			return $items->get();
		}

		$items = $items->paginate($limit)->appends($request->except('page'));

		return LogResource::collection($items);
	}

	/**
	 * Get log
	 *
	 * @permission log.show
	 *
	 * @param Log $log
	 *
	 * @return LogResource
	 */
	public function show(Log $log)
	{
		// Load relations
		$log->load(['user']);

		return new LogResource($log);
	}

	/**
	 * Export logs
	 *
	 * @permission log.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `user_id` `log_type` `log_id` `log_name` `message` `action` `ip` `user_agent` `url` `method` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `user_id` `log_type` `log_id` `log_name` `message` `action` `ip` `user_agent` `url` `method`
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
				'name'  => 'Nome',
				'value' => 'log_name',
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
				'name'  => 'Ação',
				'value' => 'action',
			],
			[
				'name'  => 'Url',
				'value' => 'url',
			],
			[
				'name'  => 'Dados antigos',
				'value' => function ($item) {
					return $item->old_data ? json_encode($item->old_data) : '';
				},
			],
			[
				'name'  => 'Dados novos',
				'value' => function ($item) {
					return $item->new_data ? json_encode($item->new_data) : '';
				},
			],
			[
				'name'  => 'Criação',
				'type'  => 'datetime',
				'value' => 'created_at',
			],
		];

		return $this->exportData($columns, $items, 'logs', 'Exportou registro de atividades');
	}
}
