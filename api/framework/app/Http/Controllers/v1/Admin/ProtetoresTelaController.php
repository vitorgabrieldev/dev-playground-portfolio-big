<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\ProtetoresTelaResource;
use App\Models\ProtetoresTela;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Class ProtetoresTelaController
 *
 * @resource Protetores de tela
 * @package  App\Http\Controllers\v1\Admin
 */
class ProtetoresTelaController extends Controller
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
	 * Get all protestores de tela
	 *
	 * @permission protetores_tela.list
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

		$items = ProtetoresTela::query();

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

		return ProtetoresTelaResource::collection($items);
	}

	/**
	 * Create protetor de tela
	 *
	 * @permission protetores_tela.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  file file required|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  capa file required|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  order integer sometimes|nullable|integer integer
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
			'file'		=> 'required|mimes:jpg,jpeg,png|max:4000',
			'capa'		=> 'required|mimes:jpg,jpeg,png|max:4000',
			'order'     => 'sometimes|nullable|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$protetortela       = new ProtetoresTela($request->only(['name',
			'descricao',
			'order',
			'is_active']));
			$protetortela->uuid = uuid();
			$protetortela->save();

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'protetores';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($protetortela->capa)
				{
					unlink($protetortela->capa);
				}

				$protetortela->update([
					'capa' => $path
				]);
			}

			// sobe as midias
			if ($request->hasFile('file'))
            {
				$file = $request->file('file');
				$path = 'protetores';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($protetortela->file)
				{
					unlink($protetortela->file);
				}

				$protetortela->update([
					'file' => $path
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
		$this->log(__FUNCTION__, $protetortela->name, 'Criou um novo protetor de tela', $protetortela);

		return (new ProtetoresTelaResource($protetortela))->response()->setStatusCode(201);
	}

	/**
	 * active/deactive protetor de tela
	 *
	 * @permission protetores_tela.edit
	 *
	 * @param Request $request
	 * @param ProtetoresTela    $protetortela
	 *
	 * @bodyParam  uuid string string uuid
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return ProtetoresTelaResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function activeDisabled(Request $request, ProtetoresTela $protetortela)
	{
		$this->validate($request, [
			'uuid'   		=> 'required|string|max:191',
			'is_active' 	=> 'required|boolean',
		]);

		$protetortela_old = clone $protetortela;

		DB::beginTransaction();

		try
		{
			// Fill
			$protetortela->fill($request->only([
				'is_active',
			]));

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $protetortela->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $protetortela_old->name, 'Ativou/desativou um protetor de tela', $protetortela, $protetortela_old);
		}

		return new ProtetoresTelaResource($protetortela);
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
			'next_order' => (ProtetoresTela::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Get protetor de tela
	 *
	 * @param ProtetoresTela $protetortela
	 *
	 * @permission protetores_tela.show
	 *
	 * @return ProtetoresTelaResource
	 */
	public function show(ProtetoresTela $protetortela)
	{
		return new ProtetoresTelaResource($protetortela);
	}

	/**
	 * Update protetor de tela
	 *
	 * @permission protetores_tela.edit
	 *
	 * @param Request $request
	 * @param ProtetoresTela     $protetortela
	 *
	 * @bodyParam  name string sometimes|nullable|string|max:191 string
	 * @bodyParam  file file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  order integer sometimes|nullable|integer integer
	 * @bodyParam  is_active boolean boolean boolean Default true
	 *
	 * @return ProtetoresTelaResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, ProtetoresTela $protetortela)
	{
		$this->validate($request, [
			'name'		=> 'sometimes|nullable|string|max:191',
			'file'		=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'capa'		=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'order'		=> 'sometimes|nullable|integer',
			'is_active'	=> 'sometimes|nullable|boolean',
		]);

		$protetortela_old = clone $protetortela;

		DB::beginTransaction();

		try
		{
			// Update
			$protetortela->fill($request->only(['name',
			'descricao',
			'order',
			'is_active']));
			$protetortela->save();

			// sobe as midias
			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'protetores';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($protetortela->capa)
				{
					unlink($protetortela->capa);
				}

				$protetortela->update([
					'capa' => $path
				]);
			}

			// sobe as midias
			if ($request->hasFile('file'))
            {
				$file = $request->file('file');
				$path = 'protetores';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($protetortela->file)
				{
					unlink($protetortela->file);
				}

				$protetortela->update([
					'file' => $path
				]);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $protetortela->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $protetortela_old->name, 'Editou um protetor de tela', $protetortela, $protetortela_old);
		}

		return new ProtetoresTelaResource($protetortela);
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

		$items = ProtetoresTela::query();

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

		return ProtetoresTelaResource::collection($items);
	}

	/**
	 * Remove protetor de tela
	 *
	 * @permission protetores_tela.delete
	 *
	 * @param ProtetoresTela $protetortela
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(ProtetoresTela $protetortela)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$protetortela->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $protetortela->name, 'Deletou um protetor de tela', $protetortela);

		return response(null, 204);
	}

	/**
	 * Export protestores de tela
	 *
	 * @permission protetores_tela.export
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
				'name'  => 'Descrição',
				'value' => 'descricao',
			],
			[
				'name'  => 'Ordem',
				'value' => 'order',
			],
			[
				'name'  => 'Arquivo',
				'value' => function ($item) {
					return $item->file ? asset($item->file) : "";
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

		return $this->exportData($columns, $items, 'categories', 'Exportou os protestores de tela');
	}
}