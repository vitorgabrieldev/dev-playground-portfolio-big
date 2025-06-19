<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\PerfisAcessoResource;
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
 * @package  App\Http\Controllers\v1\Admin
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
	];

	/**
	 * Get all perfis de acesso.
	 *
	 * @permission perfil_acesso.list
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

		$items = PerfisAcesso::query();

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

		return PerfisAcessoResource::collection($items);
	}

	/**
	 * Get additional information
	 *
	 * @response {
	 * "next_order":5
	 * }
	 *
	 * @return array
	 */
	public function additionalInformation()
	{
		return [
			'next_order' => (PerfisAcesso::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create perfis de acesso.
	 *
	 * @permission perfil_acesso.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean Default true
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name'      => 'required|string|max:191',
			'order'     => 'required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$perfil       = new PerfisAcesso($request->only(['name', 'order', 'is_active']));
			$perfil->uuid = uuid();
			$perfil->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $perfil->name, 'Criou um novo perfil de acesso', $perfil);

		return (new PerfisAcessoResource($perfil))->response()->setStatusCode(201);
	}

	/**
	 * Autocomplete categories
	 *
	 * List a maximum of `50` items.
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function autocomplete(Request $request)
	{
		$this->validate($request, [
			'orderBy'   => 'sometimes|nullable|string',
			'search'    => 'sometimes|nullable|string',
			'is_active' => 'sometimes|integer',
		]);

		$this->fieldSortable = [
			'id',
			'name',
			'is_active',
			'created_at',
			'updated_at',
		];

		$this->fieldSearchable = [
			'uuid',
			'name',
		];

		$items = PerfisAcesso::query();

		// Select
		$items->select('id', 'uuid', 'name', 'is_active', 'created_at', 'updated_at');

		$items->isActive();

		$search    = $request->input('search');
		$orderBy   = $this->getOrderBy($request->input('orderBy'));

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

		$items = $items->limit(50)->get();

		return PerfisAcessoResource::collection($items);
	}

	/**
	 * Get perfis de acesso.
	 *
	 * @permission perfil_acesso.show
	 *
	 * @param PerfisAcesso $perfil
	 *
	 * @return PerfisAcessoResource
	 */
	public function show(PerfisAcesso $perfil)
	{
		return new PerfisAcessoResource($perfil);
	}

	/**
	 * Update perfis de acesso.
	 *
	 * @permission perfil_acesso.edit
	 *
	 * @param Request $request
	 * @param PerfisAcesso     $perfil
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return PerfisAcessoResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, PerfisAcesso $perfil)
	{
		$this->validate($request, [
			'name'      => 'sometimes|required|string|max:191',
			'order'     => 'sometimes|required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		$perfil_old = clone $perfil;

		DB::beginTransaction();

		try
		{
			// Update
			$perfil->fill($request->only(['name', 'order', 'is_active']));
			$perfil->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $perfil->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $perfil_old->name, 'Editou um perfil de acesso', $perfil, $perfil_old);
		}

		return new PerfisAcessoResource($perfil);
	}

	/**
	 * Remove perfis de acesso.
	 *
	 * @permission perfil_acesso.delete
	 *
	 * @param PerfisAcesso $perfil
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(PerfisAcesso $perfil)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$perfil->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $perfil->name, 'Deletou um perfil de acesso', $perfil);

		return response(null, 204);
	}

	/**
	 * Export perfis de acesso.
	 *
	 * @permission perfil_acesso.export
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
				'name'  => 'Nome',
				'value' => 'name',
			],
			[
				'name'  => 'Ordem',
				'value' => function ($item) {
					return $item->order ? 1 : 0;
				},
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

		return $this->exportData($columns, $items, 'perfis de acesso', 'Exportou perfis de acesso');
	}
}
