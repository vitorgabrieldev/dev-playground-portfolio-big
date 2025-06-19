<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\NotificationsResource;
use App\Models\Notifications;
use App\Models\PushGeneral;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class NotificationsController
 *
 * @resource Notifications
 * @package  App\Http\Controllers\v1\Admin
 */
class NotificationsController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
        'name',
		'created_at',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
		'descricao',
		'text',
	];

	/**
	 * Get all notifications
	 *
	 * @param Request $request
	 *
	 * @permission notifications.list
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'   => 'sometimes|nullable|integer',
			'page'    => 'sometimes|nullable|integer',
			'is_active'    => 'sometimes|integer',
			'orderBy' => 'sometimes|nullable|string',
			'search'  => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = Notifications::query();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));
		$is_active	= $request->input('is_active');
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

		return NotificationsResource::collection($items);
	}

	/**
	 * Create notificacao
	 *
	 * @permission notifications.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  descricao string required|string|max:191 string
	 * @bodyParam  text string required|string|max:10000 realText(2000)
	 * @bodyParam  video string required|string|max:191 string
	 * @bodyParam  titulo_button string required|string|max:191 string
	 * @bodyParam  link_button string required|string|max:191 string
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  send_push boolean boolean boolean Default true
	 * @bodyParam  data_envio string nullable|string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Future date
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name'				=> 'required|string|max:191',
			'text'				=> 'required|string|max:10000',
			'descricao'			=> 'sometimes|string|max:191',
			'video'				=> 'sometimes|string|max:191',
			'titulo_button'		=> 'sometimes|string|max:191',
			'link_button'		=> 'sometimes|string|max:191',
            'capa'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'is_active'			=> 'sometimes|required|boolean',
			'send_push'			=> 'sometimes|required|boolean',
			'data_envio' => 'sometimes|nullable|string|date|after:now',
		], [
			'data_envio.after' => 'Deve conter uma data futura.',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$notification       = new Notifications($request->only([
				'name',
				'text',
				'descricao',
				'video',
				'titulo_button',
				'link_button',
				'data_envio',
				'send_push',
				'is_active',
			]));
			$notification->uuid = uuid();
			$notification->save();

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'notifications';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($notification->capa)
				{
					unlink($notification->capa);
				}

				$notification->update([
					'capa' => $path
				]);
			}

			// se for enviar o push, crie um general
			if((bool)$request->input('send_push'))
			{
				$push               = new PushGeneral();
				$push->uuid         = $notification->uuid;
				$push->title		= $request->input('name');
				$push->body         = $request->input('descricao');
				$push->url			= $request->input('link_button');
				$push->scheduled_at = $request->input('data_envio') ? Carbon::parse($request->input('data_envio'))->setTimezone('UTC')->toDateTimeString() : null;
				$push->save();
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $notification->uuid, 'Criou uma nova notificacao', $notification);

		return (new NotificationsResource($notification))->response()->setStatusCode(201);
	}

	/**
	 * Get notificacao
	 *
	 * @permission notifications.show
	 *
	 * @param Notifications $notification
	 *
	 * @return NotificationsResource
	 */
	public function show(Notifications $notification)
	{
		return new NotificationsResource($notification);
	}

	/**
	 * Update notification
	 *
	 * @permission notifications.edit
	 *
	 * @param Request   $request
	 * @param Notifications $notification
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  descricao string required|string|max:191 string
	 * @bodyParam  text string required|string|max:10000 realText(2000)
	 * @bodyParam  video string required|string|max:191 string
	 * @bodyParam  titulo_button string required|string|max:191 string
	 * @bodyParam  link_button string required|string|max:191 string
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  send_push boolean boolean boolean Default true
	 * @bodyParam  data_envio string nullable|string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Future date
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 *
	 * @return NotificationsResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function update(Request $request, Notifications $notification)
	{
		$this->validate($request, [
			'name'				=> 'sometimes|nullable|string|string|max:191',
			'text'				=> 'sometimes|nullable|string|string|max:10000',
			'descricao'			=> 'sometimes|nullable|string|max:191',
			'video'				=> 'sometimes|nullable|string|max:191',
			'titulo_button'		=> 'sometimes|nullable|string|max:191',
			'link_button'		=> 'sometimes|nullable|string|max:191',
            'capa'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'is_active'			=> 'sometimes|nullable|boolean',
			'send_push'			=> 'sometimes|nullable|boolean',
			'data_envio' => 'sometimes|nullable|string|date|after:now',
		], [
			'data_envio.after' => 'Deve conter uma data futura.',
		]);

		$notification_old = clone $notification;

		DB::beginTransaction();

		try
		{
			// Update
			$notification->update($request->only([
				'name',
				'text',
				'descricao',
				'video',
				'titulo_button',
				'link_button',
				'data_envio',
				'send_push',
				'is_active',
			]));

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'notifications';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($notification->capa)
				{
					unlink($notification->capa);
				}

				$notification->update([
					'capa' => $path
				]);
			}

			// se for enviar o push, crie um general
			if($request->input('send_push') == 1)
			{
				$push = PushGeneral::where('uuid', $notification->uuid)->withTrashed()->first() ?? null;
				if($push)
					$push->deleted_at = null;
			}
			elseif($request->input('send_push') == 0)
			{
				$push = PushGeneral::where('uuid', $notification->uuid)->first() ?? null;
				if($push)
					$push->delete();
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $notification->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $notification_old->name, 'Editou uma notificacao', $notification, $notification_old);
		}

		return new NotificationsResource($notification);
	}

	/**
	 * Remove notification
	 *
	 * @permission notifications.delete
	 *
	 * @param Notifications $notification
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(Notifications $notification)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$notification->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $notification->name, 'Deletou uma notificacao', $notification);

		return response(null, 204);
	}

	/**
	 * Export notifications
	 *
	 * @permission notifications.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `is_active` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `email`
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
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
				'name'  => 'Titulo',
				'value' => 'name',
			],
			[
				'name'  => 'Sinopse',
				'value' => 'descricao',
			],
			[
				'name'  => 'Texto',
				'value' => 'text',
			],
			[
				'name'  => 'Data de ínicio',
				'type'  => 'datetime',
				'value' => 'data_envio',
			],
			[
				'name'  => 'Data final',
				'type'  => 'datetime',
				'value' => 'data_expiracao',
			],
			[
				'name'  => 'Ativo',
				'value' => function ($item) {
					return $item->is_active ? "sim" : "não";
				},
			],
			[
				'name'  => 'Criação',
				'type'  => 'datetime',
				'value' => 'created_at',
			],
			[
				'name'  => 'Última modificação',
				'type'  => 'datetime',
				'value' => 'updated_at',
			],
		];

		return $this->exportData($columns, $items, 'notifications', 'Exportou as notificacoes');
	}
}
