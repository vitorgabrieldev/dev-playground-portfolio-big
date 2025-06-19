<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\HowItWorksResource;
use App\Models\HowItWorks;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class HowItWorksController
 *
 * @resource How It Works
 * @package  App\Http\Controllers\v1\Admin
 */
class HowItWorksController extends Controller
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
	 * Get all how it works
	 *
	 * @permission how-it-works.list
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

		$items = HowItWorks::query();

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

		return HowItWorksResource::collection($items);
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
			'next_order' => (HowItWorks::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create how it works
	 *
	 * @permission how-it-works.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 realText(2000)
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
			'text'      => 'nullable|string|max:10000',
			'order'     => 'required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$how_it_works       = new HowItWorks($request->only(['name', 'text', 'order', 'is_active']));
			$how_it_works->uuid = uuid();
			$how_it_works->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $how_it_works->name, 'Criou um nova como funciona', $how_it_works);

		return (new HowItWorksResource($how_it_works))->response()->setStatusCode(201);
	}

	/**
	 * Get how it works
	 *
	 * @permission how-it-works.show
	 *
	 * @param HowItWorks $how_it_works
	 *
	 * @return HowItWorksResource
	 */
	public function show(HowItWorks $how_it_works)
	{
		return new HowItWorksResource($how_it_works);
	}

	/**
	 * Update how it works
	 *
	 * @permission how-it-works.edit
	 *
	 * @param Request    $request
	 * @param HowItWorks $how_it_works
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 text
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return HowItWorksResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, HowItWorks $how_it_works)
	{
		$this->validate($request, [
			'name'      => 'sometimes|required|string|max:191',
			'text'      => 'sometimes|nullable|string|max:10000',
			'order'     => 'sometimes|required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		$how_it_works_old = clone $how_it_works;

		DB::beginTransaction();

		try
		{
			// Update
			$how_it_works->fill($request->only(['name', 'text', 'order', 'is_active']));
			$how_it_works->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $how_it_works->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $how_it_works_old->name, 'Editou um como funciona', $how_it_works, $how_it_works_old);
		}

		return new HowItWorksResource($how_it_works);
	}

	/**
	 * Remove how it works
	 *
	 * @permission how-it-works.delete
	 *
	 * @param HowItWorks $how_it_works
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(HowItWorks $how_it_works)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$how_it_works->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $how_it_works->name, 'Deletou um como funciona', $how_it_works);

		return response(null, 204);
	}

	/**
	 * Export how it works
	 *
	 * @permission how-it-works.export
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
				'name'  => 'Texto',
				'value' => 'text',
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

		return $this->exportData($columns, $items, 'how-it-works', 'Exportou como funciona');
	}
}
