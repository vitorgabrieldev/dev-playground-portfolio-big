<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\BannersResource;
use App\Models\Banners;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class BannersController
 *
 * @resource Banners
 * @package  App\Http\Controllers\v1\Admin
 */
class BannersController extends Controller
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
	 * Get all banners
	 *
	 * @permission banners.list
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

		$items = Banners::query();

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

		return BannersResource::collection($items);
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
			'next_order' => (Banners::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create banners
	 *
	 * @permission banners.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  frase string nullable|string|max:191 string
	 * @bodyParam  file file mimes:jpg,jpeg,png,mp4,gif,avi,mov,webm,mpg,ogg,mkv,flv|max:8mb file
	 * @bodyParam  link string nullable|string|max:191 string
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
			'name'		=> 'required|string|max:191',
			'frase'		=> 'nullable|string|max:191',
			'file'		=> 'mimes:jpg,jpeg,png,mp4,avi,mov,webm,mpg,ogg,mkv,flv|max:8000',
			'link'      => 'nullable|string|max:191',
			'order'     => 'required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$banners       = new Banners($request->only(['name', 'link', 'frase', 'order', 'is_active']));
			$banners->uuid = uuid();
			$banners->save();

			// sobe as midias
			if ($request->hasFile('file'))
            {
				$file = $request->file('file');
				$path = 'media';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);
				$type = "image";
				$extensoes_videos = ['mp4', 'avi', 'mov', 'webm', 'mpg', 'ogg', 'mkv', 'flv'];
				$extensoes_gif = ['gif'];

				if (in_array($file->getClientOriginalExtension(), $extensoes_videos))
				{
					// A extensão é um vídeo
					$type = 'video';
				}

				if (in_array($file->getClientOriginalExtension(), $extensoes_gif))
				{
					// A extensão é um gif
					$type = 'gif';
				}

				// exclui a antiga se existir
				if($banners->file)
				{
					unlink($banners->file);
				}

				$banners->update([
					'type' => $type,
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
		$this->log(__FUNCTION__, $banners->name, 'Criou um banner', $banners);

		return (new BannersResource($banners))->response()->setStatusCode(201);
	}

	/**
	 * Get banners
	 *
	 * @permission banners.show
	 *
	 * @param Banners $banners
	 *
	 * @return BannersResource
	 */
	public function show(Banners $banners)
	{
		return new BannersResource($banners);
	}

	/**
	 * Update banners
	 *
	 * @permission banners.edit
	 *
	 * @param Request $request
	 * @param Banners     $banners
	 *
	 * @bodyParam  name string string|max:191 string
	 * @bodyParam  frase string nullable|string|max:191 string
	 * @bodyParam  file file mimes:jpg,jpeg,png,mp4,gif,avi,mov,webm,mpg,ogg,mkv,flv|max:8mb file
	 * @bodyParam  link string nullable|string|max:191 url
	 * @bodyParam  order integer required|integer integer
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return BannersResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, Banners $banners)
	{
		$this->validate($request, [
			'name'      => 'sometimes|required|string|max:191',
			'frase'		=> 'sometimes|nullable|string|max:191',
			'file'		=> 'mimes:jpg,jpeg,png,mp4,avi,mov,webm,mpg,ogg,mkv,flv|max:8000',
			'link'      => 'sometimes|nullable|string|max:191',
			'order'     => 'sometimes|required|integer',
			'is_active' => 'sometimes|required|boolean',
		]);

		$banners_old = clone $banners;

		DB::beginTransaction();

		try
		{
			// Update
			$banners->fill($request->only(['name', 'frase','link', 'order', 'is_active']));
			$banners->save();

			// sobe as midias
			if ($request->hasFile('file'))
            {
				$file = $request->file('file');
				$path = 'media';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);
				$type = "image";
				$extensoes_videos = ['mp4', 'avi', 'mov', 'webm', 'mpg', 'ogg', 'mkv', 'flv'];
				$extensoes_gif = ['gif'];

				if (in_array($file->getClientOriginalExtension(), $extensoes_videos))
				{
					// A extensão é um vídeo
					$type = 'video';
				}

				if (in_array($file->getClientOriginalExtension(), $extensoes_gif))
				{
					// A extensão é um gif
					$type = 'gif';
				}

				// exclui a antiga se existir
				if($banners->file)
				{
					unlink($banners->file);
				}

				$banners->update([
					'type' => $type,
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

		if( $banners->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $banners_old->name, 'Editou um banner', $banners, $banners_old);
		}

		return new BannersResource($banners);
	}

	/**
	 * Remove banners
	 *
	 * @permission banners.delete
	 *
	 * @param Banners $banners
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(Banners $banners)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$banners->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $banners->name, 'Deletou um banner', $banners);

		return response(null, 204);
	}

	/**
	 * Export banners
	 *
	 * @permission banners.export
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
				'name'  => 'Frase',
				'value' => 'frase',
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

		return $this->exportData($columns, $items, 'banners', 'Exportou banners');
	}
}
