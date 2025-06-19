<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\FaqResource;
use App\Models\Faq;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class FaqController
 *
 * @resource Faq
 * @package  App\Http\Controllers\v1\Admin
 */
class FaqController extends Controller
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
		'text',
	];

	/**
	 * Get all faq
	 *
	 * @permission faq.list
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

		$items = Faq::query();

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

		return FaqResource::collection($items);
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
			'next_order' => (Faq::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create faq
	 *
	 * @permission faq.create
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
			$faq       = new Faq($request->only(['name', 'text', 'order', 'is_active']));
			$faq->uuid = uuid();
			$faq->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $faq->name, 'Criou um nova pergunta frequente', $faq);

		return (new FaqResource($faq))->response()->setStatusCode(201);
	}

	/**
	 * Get faq
	 *
	 * @permission faq.show
	 *
	 * @param Faq $faq
	 *
	 * @return FaqResource
	 */
	public function show(Faq $faq)
	{
		return new FaqResource($faq);
	}

	/**
	 * Update faq
	 *
	 * @permission faq.edit
	 *
	 * @param Request $request
	 * @param Faq     $faq
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 text
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return FaqResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, Faq $faq)
	{
		$this->validate($request, [
			'name'      => 'sometimes|required|string|max:191',
			'text'      => 'sometimes|nullable|string|max:10000',
			'order'     => 'sometimes|required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		$faq_old = clone $faq;

		DB::beginTransaction();

		try
		{
			// Update
			$faq->fill($request->only(['name', 'text', 'order', 'is_active']));
			$faq->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $faq->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $faq_old->name, 'Editou uma pergunta frequente', $faq, $faq_old);
		}

		return new FaqResource($faq);
	}

	/**
	 * Remove faq
	 *
	 * @permission faq.delete
	 *
	 * @param Faq $faq
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(Faq $faq)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$faq->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $faq->name, 'Deletou uma pergunta frequente', $faq);

		return response(null, 204);
	}

	/**
	 * Export faq
	 *
	 * @permission faq.export
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

		return $this->exportData($columns, $items, 'faq', 'Exportou perguntas frequentes');
	}
}
