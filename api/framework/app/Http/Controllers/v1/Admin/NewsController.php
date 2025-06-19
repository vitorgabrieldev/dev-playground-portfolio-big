<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Throwable;

/**
 * Class NewsController
 *
 * @resource Noticias
 * @package  App\Http\Controllers\v1\Admin
 */
class NewsController extends Controller
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
		'data_inicio'
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
		'text'
	];

	/**
	 * Get all noticias
	 *
	 * @param Request $request
	 *
	 * @permission news.list
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `text`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 * @urlParam   data_inicio[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   data_inicio[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
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
			'data_inicio'   => 'sometimes|array',
			'data_inicio.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = News::query();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));
		$limit   = $this->getLimit($request->input('limit'));
		$is_active	= $request->input('is_active');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;
		$data_inicio_start = $request->has('data_inicio.0') ? Carbon::parse($request->input('data_inicio.0'))->setTimezone('UTC') : null;
		$data_inicio_end   = $request->has('data_inicio.1') ? Carbon::parse($request->input('data_inicio.1'))->setTimezone('UTC') : null;

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

		// data_inicio
		if( $data_inicio_start )
		{
			$items->where('data_inicio', '>=', $data_inicio_start);
		}
		if( $data_inicio_end )
		{
			$items->where('data_inicio', '<=', $data_inicio_end);
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

		return NewsResource::collection($items);
	}

	/**
	 * Create noticias
	 *
	 * @permission news.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  text string required|string|max:10000 realText(2000)
	 * @bodyParam  sinopse string required|string|max:191 string
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  data_inicio string required|string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` date
	 * @bodyParam  data_expiracao string nullable|string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` date
	 * @bodyParam  imagens[] file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
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
			'data_inicio'		=> 'required|date_format:Y-m-d\TH:i:sP',
			'sinopse'			=> 'sometimes|string|max:191',
			'data_expiracao'	=> 'sometimes|nullable|date_format:Y-m-d\TH:i:sP',
            'imagens'			=> 'sometimes|nullable|array',
            'imagens.*'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'is_active'			=> 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$new       = new News($request->only(['name', 'text', 'data_inicio', 'sinopse', 'data_expiracao', 'is_active']));
			$new->uuid = uuid();
			$new->save();

			if ($request->hasFile('imagens'))
            {
                foreach ($request->file('imagens') as $key => $file)
                {
                    $file_name = $new->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
                    $file_path = storage_path('app/public/news/' . $file_name);
                    $file_url  = 'storage/news/' . $file_name;

                    // Save image
                    Image::make($file)->fit(1000, 1000)->save($file_path);

                    $new->media()->create([
                        'uuid'        => uuid(),
                        'type'        => 'image',
                        'file'        => $file_url,
                        'name'        => $file->getClientOriginalName(),
                        'description' => null,
                        'mime'        => $file->getClientMimeType(),
                        'size'        => $file->getSize(),
                        'width'       => 1000,
                        'height'      => 1000,
                        'order'       => $key,
                        'is_active'   => true,
                    ]);
                }
			}
			$new->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $new->uuid, 'Criou uma nova noticia', $new);

		return (new NewsResource($new))->response()->setStatusCode(201);
	}

	/**
	 * Get noticia
	 *
	 * @permission news.show
	 *
	 * @param News $new
	 *
	 * @return NewsResource
	 */
	public function show(News $new)
	{
		$new->load(['media']);

		return new NewsResource($new);
	}

	/**
	 * Update noticia
	 *
	 * @permission news.edit
	 *
	 * @param Request $request
	 * @param News $new
	 *
	 * @bodyParam  name string nullable|string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 realText(2000)
	 * @bodyParam  sinopse string nullable|string|max:191 string
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  data_inicio string nullable|string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` date
	 * @bodyParam  data_expiracao string nullable|string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` date
	 * @bodyParam  imagens[] file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  imagens_no_delete[] array required|array|max:191 uuid
	 *
	 * @return NewsResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, News $new)
	{
		$this->validate($request, [
			'name'				=> 'sometimes|nullable|string|max:191',
			'text'				=> 'sometimes|nullable|string|max:10000',
			'data_inicio'		=> 'sometimes|nullable|date_format:Y-m-d\TH:i:sP',
			'sinopse'			=> 'sometimes|string|max:191',
			'data_expiracao'	=> 'sometimes|nullable|date_format:Y-m-d\TH:i:sP',
            'imagens'			=> 'sometimes|nullable|array',
            'imagens.*'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'is_active'			=> 'sometimes|required|boolean',
			'imagens_no_delete' => 'sometimes|nullable|array',
			'imagens_no_delete.*' => 'sometimes|nullable|string|max:191',

		]);

		$new_old = clone $new;

		$medias_no_delete = collect($request->input('imagens_no_delete'))->unique()->all();

		DB::beginTransaction();

		try
		{
			// Update
			$new->fill($request->only(['name', 'text', 'data_inicio', 'sinopse', 'data_expiracao', 'is_active']));
			$new->save();

			// antes de adicionar imagens novas, verifica se tem imagens para excluir
			// faz um foreach pelas imagens existentes, se existir diferença de uuid no array que veio, entao exclui o que NÃO VEIO
			$medias_exist = $new->media()->get();

			foreach ($medias_exist as $key => $media_db)
			{
				if(!in_array($media_db->uuid, $medias_no_delete))
				{
					$media_db->delete();
				}
			}
		
			// add
			if ($request->hasFile('imagens'))
            {
				$order = $new->media()->max('order') ?? 0;

				foreach( $request->file('imagens') as $index => $file )
				{
					$file_name   = uuid() . '.jpg';
					$file_folder = 'storage/news/';
					$file_path   = public_path($file_folder) . $file_name;
					$file_url    = $file_folder . $file_name;

					// Save image
					$image_instance = Image::make($file)->resize(1000, 1000, function ($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					})->save($file_path);

					$new->media()->create([
						'uuid'   => uuid(),
						'type'   => 'image',
						'file'   => $file_url,
						'width'  => $image_instance->width(),
						'height' => $image_instance->height(),
						'order'  => $order + $index,
					]);
				}
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $new->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $new_old->name, 'Editou a noticia', $new, $new_old);
		}

		return new NewsResource($new);
	}

	/**
	 * Remove noticia
	 *
	 * @permission news.delete
	 *
	 * @param News $new
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(News $new)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$new->media()->delete();
			$new->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $new->name, 'Deletou uma noticia', $new);

		return response(null, 204);
	}

	/**
	 * Export noticia
	 *
	 * @permission news.export
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
				'value' => 'sinopse',
			],
			[
				'name'  => 'Texto',
				'value' => 'text',
			],
			[
				'name'  => 'Data de ínicio',
				'type'  => 'datetime',
				'value' => 'data_inicio',
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

		return $this->exportData($columns, $items, 'news', 'Exportou as noticias');
	}
}