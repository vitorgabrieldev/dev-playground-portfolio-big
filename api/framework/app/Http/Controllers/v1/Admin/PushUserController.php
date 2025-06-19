<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\GeneralException;
use App\Http\Resources\Admin\PushUserResource;
use App\Models\Customer;
use App\Models\PushUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Class PushUserController
 *
 * @resource Push User
 * @package  App\Http\Controllers\v1\Admin
 */
class PushUserController extends Controller
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
		'user.name',
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
		'user.name',
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
		$table = (new PushUser())->getTable();
		$tableCustomer = (new Customer())->getTable();
	
		// Mapeamento de colunas considerando múltiplos tipos de usuário
		$columnMap = [
			'user.name' => DB::raw("
				CASE
					WHEN {$table}.user_type = '" . addslashes(Customer::class) . "' 
					THEN (SELECT name FROM {$tableCustomer} WHERE {$tableCustomer}.id = {$table}.user_id)
					ELSE NULL
				END
			"),
		];
	
		return $columnMap[$column] ?? "{$table}.{$column}";
	}

	/**
	 * Get all push
	 *
	 * @permission push-user.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
     * @urlParam   user_id string string static(uuid)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `user.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `user.name`
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
            'user_id'      => 'sometimes|nullable|uuid',
			'page'         => 'sometimes|nullable|integer',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = PushUser::query();

        $items->with(['user']);

		$search           = $request->input('search');
        $user_id           = $request->input('user_id');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

        if($user_id)
        {
            $user = Customer::where('uuid',$user_id)->first();

            if($user)
            {
                $items->where('user_id', $user->id);
                $items->where('user_type', get_class($user));
            }
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

		return PushUserResource::collection($items);
	}

	/**
	 * Create push
	 *
	 * @permission push-user.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  user_id integer required|uuid uuid `Customer user` relationship
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
			'user_id'      => [
				'required',
				'uuid',
				function ($attribute, $value, $fail) {
					$existsCustomer = (new Customer())
						->where('uuid', $value)
						->where('is_active', 1)
						->whereNull('deleted_at')
						->exists();
					if (!$existsCustomer) {
						$fail(__('O usuário não foi encontrado ou está inativo.'));
					}
				},
			],
			'title'        => 'required|string|max:50',
			'body'         => 'required|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

		$user = Customer::where('uuid',$request->input("user_id"))->first();

		// Fill
		$push               = new PushUser($request->only(['title', 'body', 'url']));
		$push->uuid         = uuid();
		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;
		$push->user_id      = $user->id;
		$push->user_type    = get_class($user);
		$push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Criou um novo push por usuário', $push);

		return (new PushUserResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Update push
	 *
	 * @permission push-user.edit
	 *
	 * @param Request $request
	 *
	 * @bodyParam  uuid integer required|string static(uuid)
	 * @bodyParam  user_id integer nullable|uuid uuid `Customer user` relationship
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
			'user_id'      => [
				'nullable',
				'uuid',
				function ($attribute, $value, $fail) {
					$existsCustomer = (new Customer())
						->where('uuid', $value)
						->where('is_active', 1)
						->whereNull('deleted_at')
						->exists();
					if (!$existsCustomer) {
						$fail(__('O usuário não foi encontrado ou está inativo.'));
					}
				},
			],
			'title'        => 'nullable|string|max:50',
			'body'         => 'nullable|string|max:100',
			'url'          => 'sometimes|nullable|string|url|max:191',
			'scheduled_at' => 'sometimes|nullable|string|date|after:now',
		], [
			'scheduled_at.after' => 'Deve conter uma data futura.',
		]);

		if($request->has("user_id"))
		{
			$user = Customer::where('uuid',$request->input("user_id"))->first();
		}

		// Fill
		$push               = (new PushUser())->where("uuid", $request->input("uuid"))->firstOrFail();
		$push_old = clone $push;

		$push->fill($request->only(['title', 'body', 'url']));

		$push->scheduled_at = $request->input('scheduled_at') ? Carbon::parse($request->input('scheduled_at'))->setTimezone('UTC')->toDateTimeString() : null;

		if($request->has("user_id"))
		{
			$push->user_id      = $user->id;
			$push->user_type    = get_class($user);
		}

		$push->save();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Editou um push por usuário', $push, $push_old);

		return (new PushUserResource($push))->response()->setStatusCode(201);
	}

	/**
	 * Get push
	 *
	 * @permission push-user.show
	 *
	 * @param PushUser $push
	 *
	 * @return PushUserResource
	 */
	public function show(PushUser $push)
	{
		// Load relations
		$push->load(['user']);

		return new PushUserResource($push);
	}

	/**
	 * Remove push
	 *
	 * @permission push-user.delete
	 *
	 * @param PushUser $push
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(PushUser $push)
	{
		if( $push->send_at )
		{
			throw GeneralException::make('push_cant_delete_past_date');
		}

		// Delete
		$push->delete();

		// Save log
		$this->log(__FUNCTION__, $push->title, 'Deletou um push por usuário', $push);

		return response(null, 204);
	}

	/**
	 * Export push
	 *
	 * @permission push-user.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `body` `scheduled_at` `send_at` `created_at` `user.name`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `body` `user.name`
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

		return $this->exportData($columns, $items, 'push-user', 'Exportou push por usuário');
	}
}
