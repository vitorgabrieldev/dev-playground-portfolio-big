<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\TreinamentoResource;
use App\Models\CategoriasTreinamento;
use App\Models\Treinamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class TreinamentoController
 *
 * @resource Treinamento
 * @package  App\Http\Controllers\v1\Admin
 */
class TreinamentoController extends Controller
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
		'descricao',
	];

	/**
	 * Get all treinamentos
	 *
	 * @permission treinamento.list
	 * 
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   categoria_treinamento_id string string|max:191 uuid
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
			'categoria_treinamento_id'	=> 'sometimes|nullable|string|exists:' . (new CategoriasTreinamento())->getTable() . ',uuid',
			'orderBy' => 'sometimes|nullable|string',
			'is_active'    => 'sometimes|integer',
			'search'  => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = Treinamento::query();
		
		// load relations
		$items->with(['categoria']);

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));
		$is_active        = $request->input('is_active');
		$categoria_treinamento_id  = $request->input('categoria_treinamento_id');
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

		if($categoria_treinamento_id)
		{
			$categoria = CategoriasTreinamento::where("uuid", $categoria_treinamento_id)->first(["id","name"]) ?? null;
			if(!$categoria)
			{
				throw ValidationException::withMessages(['categoria_treinamento_id' => __('Categoria não encontrada. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
			}

			$items->whereRelation('categoria', 'id', $categoria->id);
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

		return TreinamentoResource::collection($items);
	}

	/**
	 * Create treinamento
	 *
	 * @permission treinamento.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  categoria_treinamento_id string required|string|max:191 uuid
	 * @bodyParam  video string required|string|max:350 string
	 * @bodyParam  capa file required|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  duracao string sometimes|nullable|string|max:191 string
	 * @bodyParam  descricao string sometimes|nullable|string|max:60000 text HTML allowed
	 * @bodyParam  order integer sometimes|nullable|integer integer
	 * @bodyParam  destaque boolean boolean boolean Default true
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
			'categoria_treinamento_id'	=> 'required|string|exists:' . (new CategoriasTreinamento())->getTable() . ',uuid',
			'capa'         => 'required|mimes:jpg,jpeg,png|max:4000',
			'video'      => 'required|string|max:191',
			'duracao'      => 'sometimes|nullable|string|max:191',
			'descricao' => 'sometimes|nullable|string|max:60000',
			'order'     => 'sometimes|nullable|integer',
			'destaque' => 'sometimes|required|boolean',
			'is_active' => 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			if($request->has('categoria_treinamento_id'))
			{
				$categoria = CategoriasTreinamento::where("uuid", $request->input('categoria_treinamento_id'))->first(["id"]) ?? null;
				if(!$categoria)
				{
					throw ValidationException::withMessages(['marca_id' => __('Marca não encontrada. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}
			}

			// Create
			$treinamento       = new Treinamento($request->only(['name',
			'video',
			'duracao',
			'descricao',
			'order',
			'is_active',
			'destaque']));
			$treinamento->categoria_treinamento_id = $categoria->id;
			$treinamento->uuid = uuid();
			$treinamento->save();

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'treinamento';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($treinamento->capa)
				{
					unlink($treinamento->capa);
				}

				$treinamento->update([
					'capa' => $path
				]);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $treinamento->name, 'Criou um novo treinamento', $treinamento);

		return (new TreinamentoResource($treinamento))->response()->setStatusCode(201);
	}

	/**
	 * active/deactive treinamento
	 *
	 * @permission treinamento.edit
	 *
	 * @param Request $request
	 * @param Treinamento    $treinamento
	 *
	 * @bodyParam  uuid string string uuid
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return TreinamentoResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function activeDisabled(Request $request, Treinamento $treinamento)
	{
		$this->validate($request, [
			'uuid'   		=> 'required|string|max:191',
			'is_active' 	=> 'required|boolean',
		]);

		$treinamento_old = clone $treinamento;

		DB::beginTransaction();

		try
		{
			// Fill
			$treinamento->fill($request->only([
				'is_active',
			]));

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $treinamento->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $treinamento_old->name, 'Ativou/desativou um treinamento', $treinamento, $treinamento_old);
		}

		return new TreinamentoResource($treinamento);
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
			'next_order' => (Treinamento::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Get treinamento
	 *
	 * @param Treinamento $treinamento
	 *
	 * @permission treinamento.show
	 *
	 * @return TreinamentoResource
	 */
	public function show(Treinamento $treinamento)
	{
		$treinamento->load(['categoria']);

		return new TreinamentoResource($treinamento);
	}

	/**
	 * Update treinamento
	 *
	 * @permission treinamento.edit
	 *
	 * @param Request $request
	 * @param Treinamento     $treinamento
	 *
	 * @bodyParam  name string sometimes|nullable|string|max:191 string
	 * @bodyParam  categoria_treinamento_id string sometimes|nullable|string|max:191 uuid
	 * @bodyParam  video string sometimes|nullable|string|max:350 string
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  duracao string sometimes|nullable|string|max:191 string
	 * @bodyParam  descricao string sometimes|nullable|string|max:60000 text HTML allowed
	 * @bodyParam  order integer sometimes|nullable|integer integer
	 * @bodyParam  destaque boolean boolean boolean Default true
	 * @bodyParam  is_active boolean boolean boolean Default true
	 *
	 * @return TreinamentoResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, Treinamento $treinamento)
	{
		$this->validate($request, [
			'name'						=> 'sometimes|nullable|string|max:191',
			'categoria_treinamento_id'	=> 'sometimes|nullable|string|exists:' . (new CategoriasTreinamento())->getTable() . ',uuid',
			'capa'						=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'video'						=> 'sometimes|nullable|string|max:191',
			'duracao'					=> 'sometimes|nullable|string|max:191',
			'descricao'					=> 'sometimes|nullable|string|max:60000',
			'order'						=> 'sometimes|nullable|integer',
			'destaque'					=> 'sometimes|nullable|boolean',
			'is_active'					=> 'sometimes|nullable|boolean',
		]);

		$treinamento_old = clone $treinamento;

		DB::beginTransaction();

		try
		{
			// Update
			$treinamento->fill($request->only(['name',
			'video',
			'duracao',
			'descricao',
			'order',
			'is_active',
			'destaque']));
			$treinamento->save();

			if($request->has('categoria_treinamento_id'))
			{
				$categoria = CategoriasTreinamento::where("uuid", $request->input('categoria_treinamento_id'))->first(["id"]) ?? null;
				if(!$categoria)
				{
					throw ValidationException::withMessages(['marca_id' => __('Marca não encontrada. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}

				$treinamento->categoria_treinamento_id = $categoria->id;
			}

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'treinamento';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($treinamento->capa)
				{
					unlink($treinamento->capa);
				}

				$treinamento->update([
					'capa' => $path
				]);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $treinamento->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $treinamento_old->name, 'Editou um treinamento', $treinamento, $treinamento_old);
		}

		return new TreinamentoResource($treinamento);
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

		$items = Treinamento::query();

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

		return TreinamentoResource::collection($items);
	}

	/**
	 * Remove treinamento
	 *
	 * @permission treinamento.delete
	 *
	 * @param Treinamento $treinamento
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(Treinamento $treinamento)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$treinamento->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $treinamento->name, 'Deletou um treinamento', $treinamento);

		return response(null, 204);
	}

	/**
	 * Export treinamentos
	 *
	 * @permission treinamento.export
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
				'name'  => 'Video',
				'value' => 'video',
			],
			[
				'name'  => 'Duração',
				'value' => 'duracao',
			],
			[
				'name'  => 'Descrição',
				'value' => 'descricao',
			],
			[
				'name'  => 'Ordem',
				'value' => 'order',
			],
			[
				'name'  => 'Destaque',
				'value' => function ($item) {
					return $item->destaque ? "sim" : "não";
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

		return $this->exportData($columns, $items, 'categories', 'Exportou os treinamentos');
	}
}