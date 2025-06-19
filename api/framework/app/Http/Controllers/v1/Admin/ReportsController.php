<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\ReportFinishResource;
use App\Http\Resources\Admin\ReportErrorResource;
use App\Http\Resources\Customer\OffersResource;
use App\Models\Offers;
use App\Models\Customer;
use App\Models\ReportError;
use App\Models\ReportFinish;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class ReportsController
 *
 * @resource Reports
 * @package  App\Http\Controllers\v1\Admin
 */
class ReportsController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'text',
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
		'text',
	];

	/**
	 * Get all reports error
	 *
	 * @permission reports-error.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `is_active` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `title` `name`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function indexErrors(Request $request)
	{
		$this->validate($request, [
			'limit'        => 'sometimes|nullable|integer',
			'page'         => 'sometimes|nullable|integer',
			'orderBy'      => 'sometimes|nullable|string',
			'offer_id'     => 'sometimes|nullable|string',
			'customer_id'  => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = ReportError::query();
		$items->with(['offer', 'customer']);

		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
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

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($orderByField['name'], $orderByField['sort']);
		}

		if ($request->has('offer_id')) {
			$offer = Offers::where('uuid', $request->input('offer_id'))->first();
			
			if ($offer) {
				$items->where('offer_id', $offer->id);
			}
		}

		if ($request->has('customer_id')) {
            $customer = Customer::where('uuid', $request->input('customer_id'))->first();

            if ($customer) {
                $items->where('customer_id', $customer->id);
            }
        }

		$items = $items->paginate($limit)->appends($request->except('page'));

		return ReportErrorResource::collection($items);
	}

	/**
	 * Get report error
	 *
	 * @permission reports-error.show
	 *
	 * @param ReportError $reportError
	 *
	 * @return ReportErrorResource
	 */
	public function showErrors(ReportError $reportError)
	{
		$reportError->load(['offer', 'customer']);
		return new ReportErrorResource($reportError);
	}

	/**
	 * Remove report error
	 *
	 * @permission reports-error.delete
	 *
	 * @param ReportError $reportError
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroyErrors(ReportError $reportError)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$reportError->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $reportError->uuid, 'Deletou/finalizou um report', $reportError);

		return response(null, 204);
	}

	/**
	 * Export report error
	 *
	 * @permission reports-error.export
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
	 * @response {
	 * "file_url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportErrors(Request $request)
	{
		$this->getExport = true;

		$items = $this->indexErrors($request);

		$columns = [
			[
				'name'  => 'UUID',
				'value' => 'uuid',
			],
			[
				'name'  => 'Ocorrido',
				'value' => 'text',
			],
			[
				'name'  => 'IP',
				'value' => 'ip',
			],
			[
				'name'  => 'Usuário',
				'value' => function ($item) {
					return $item->customer->name ?? '-';
				},
			],
			[
				'name'  => 'Oferta',
				'value' => function ($item) {
					return $item->offer->title ?? '-';
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

		return $this->exportData($columns, $items, 'reports', 'Exportou as ofertas reportadas com erros');
	}

	/**
	 * Get all reports finish
	 *
	 * @permission reports-finish.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   perfil_id string string|max:191 uuid
	 * @urlParam   category_id string string|max:191 uuid
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   under_review integer integer static(1) Available values: `0` `1`
	 * @urlParam   is_internacional integer integer static(1) Available values: `0` `1`
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   store_id string string|max:191 uuid
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `title` `price` `like` `shared` `comments` `created_at` `updated_at` 
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function indexFinish(Request $request)
	{
		$this->validate($request, [
			'limit'        => 'sometimes|nullable|integer',
			'page'         => 'sometimes|nullable|integer',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = ReportFinish::query();
		$items->with(['offer', 'customer']);

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
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

		if( $search and count($this->fieldSearchable) )
		{
			$items->where(function ($query) use ($search) {
				foreach( $this->fieldSearchable as $searchField )
				{
					$query->orWhere($searchField, 'like', '%' . $search . '%');
				}
			})->orWhereRelation('offer','title', 'like', '%' . $search . '%')
			->orWhereRelation('customer','name', 'like', '%' . $search . '%');
		}

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($orderByField['name'], $orderByField['sort']);
		}
		
		if ($request->has('offer_id')) {
			$offer = Offers::where('uuid', $request->input('offer_id'))->first();
			
			if ($offer) {
				$items->where('offer_id', $offer->id);
			}
		}

		if ($request->has('customer_id')) {
            $customer = Customer::where('uuid', $request->input('customer_id'))->first();

            if ($customer) {
                $items->where('customer_id', $customer->id);
            }
        }

		$items = $items->paginate($limit)->appends($request->except('page'));

		return ReportFinishResource::collection($items);
	}


	/**
	 * Get report offer finish
	 *
	 * @permission reports-finish.show
	 *
	 * @param ReportFinish $reportFinish
	 *
	 * @return ReportFinishResource
	 */
	public function showFinish($uuid)
	{
		$reportFinish = ReportFinish::where('uuid', $uuid)->firstOrFail();
		$reportFinish->load(['offer', 'customer']);
		return new ReportFinishResource($reportFinish);
	}

	/**
	 * Remove report offer finish
	 *
	 * @permission reports-finish.delete
	 *
	 * @param reportFinish $reportFinish
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroyFinish(reportFinish $reportFinish)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$reportFinish->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $reportFinish->uuid, 'Deletou/finalizou um report de oferta encerrada', $reportFinish);

		return response(null, 204);
	}

	/**
	 * Export report finish
	 *
	 * @permission reports-finish.export
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
	 * @response {
	 * "file_url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws \PhpOffice\PhpSpreadsheet\Exception
	 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
	 */
	public function exportFinish(Request $request)
	{
		$this->getExport = true;

		$items = $this->indexFinish($request);

		$columns = [
			[
				'name'  => 'UUID',
				'value' => 'uuid',
			],
			[
				'name'  => 'Ocorrido',
				'value' => 'text',
			],
			[
				'name'  => 'IP',
				'value' => 'ip',
			],
			[
				'name'  => 'Usuário',
				'value' => function ($item) {
					return $item->customer->name ?? '-';
				},
			],
			[
				'name'  => 'Oferta',
				'value' => function ($item) {
					return $item->offer->title ?? '-';
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

		return $this->exportData($columns, $items, 'reports', 'Exportou as ofertas reportadas encerradas');
	}
}