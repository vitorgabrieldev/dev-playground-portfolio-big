<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\CategoriasTreinamentoResource;
use App\Models\CategoriasTreinamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class CategoriasTreinamentoController
 *
 * @resource Categorias de treinamento
 * @package  App\Http\Controllers\v1\Admin
 */
class CategoriasTreinamentoController extends Controller
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
	 * Get all categories
	 *
	 * @permission categorias_treinamento.list
	 * 
	 * @param Request $request
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
			'orderBy' => 'sometimes|nullable|string',
			'is_active'    => 'sometimes|integer',
			'search'  => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = CategoriasTreinamento::query();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));
		$is_active        = $request->input('is_active');
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

		// is_active
		if( $is_active !== null )
		{
			$items->where('is_active', '=', $is_active ? 1 : 0);
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

		return CategoriasTreinamentoResource::collection($items);
	}

	/**
	 * Create categoria
	 *
	 * @permission categorias_treinamento.create
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
			$categoria       = new CategoriasTreinamento($request->only(['name','is_active', 'order']));
			$categoria->uuid = uuid();
			$categoria->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $categoria->name, 'Criou uma nova categoria', $categoria);

		return (new CategoriasTreinamentoResource($categoria))->response()->setStatusCode(201);
	}

	/**
	 * active/deactive categoria
	 *
	 * @permission categorias_treinamento.edit
	 *
	 * @param Request $request
	 * @param CategoriasTreinamento    $categoria
	 *
	 * @bodyParam  uuid string string uuid
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return CategoriasTreinamentoResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function activeDisabled(Request $request, CategoriasTreinamento $categoria)
	{
		$this->validate($request, [
			'uuid'   		=> 'required|string|max:191',
			'is_active' 	=> 'required|boolean',
		]);

		$categoria_old = clone $categoria;

		DB::beginTransaction();

		try
		{
			// Fill
			$categoria->fill($request->only([
				'is_active',
			]));

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $categoria->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $categoria_old->name, 'Ativou/desativou uma categoria', $categoria, $categoria_old);
		}

		return new CategoriasTreinamentoResource($categoria);
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
			'next_order' => (CategoriasTreinamento::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Get categoria
	 *
	 * @param CategoriasTreinamento $categoria
	 *
	 * @permission categorias_treinamento.show
	 *
	 * @return CategoriasTreinamentoResource
	 */
	public function show(CategoriasTreinamento $categoria)
	{
		return new CategoriasTreinamentoResource($categoria);
	}

	/**
	 * Update categoria
	 *
	 * @permission categorias_treinamento.edit
	 *
	 * @param Request $request
	 * @param CategoriasTreinamento     $categoria
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return CategoriasTreinamentoResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, CategoriasTreinamento $categoria)
	{
		$this->validate($request, [
			'name'      => 'sometimes|required|string|max:191',
			'order'     => 'required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		$categoria_old = clone $categoria;

		DB::beginTransaction();

		try
		{
			// Update
			$categoria->fill($request->only(['name', 'is_active', 'order']));
			$categoria->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $categoria->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $categoria_old->name, 'Editou um categoria', $categoria, $categoria_old);
		}

		return new CategoriasTreinamentoResource($categoria);
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

		$items = CategoriasTreinamento::query();

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

		return CategoriasTreinamentoResource::collection($items);
	}

	/**
	 * Remove categoria
	 *
	 * @permission categorias_treinamento.delete
	 *
	 * @param CategoriasTreinamento $categoria
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(CategoriasTreinamento $categoria)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$categoria->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $categoria->name, 'Deletou uma categoria', $categoria);

		return response(null, 204);
	}

	/**
	 * Export categories
	 *
	 * @permission categorias_treinamento.export
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
				'value' => 'order',
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

		return $this->exportData($columns, $items, 'categories', 'Exportou os categories');
	}
}