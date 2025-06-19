<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\ValeRioDoceResource;
use App\Models\ValeRioDoce;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class ValeRioDoceController
 *
 * @resource Vale do rio doce
 * @package  App\Http\Controllers\v1\Admin
 */
class ValeRioDoceController extends Controller
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
	 * Get all vale do rio doce
	 *
	 * @permission vale_rio_doce.list
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

		$items = ValeRioDoce::query();

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

		return ValeRioDoceResource::collection($items);
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
			'next_order' => (ValeRioDoce::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create vale do rio doce
	 *
	 * @permission vale_rio_doce.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  text string required|string|max:10000 realText(2000)
	 * @bodyParam  sinopse string required|string|max:191 string
	 * @bodyParam  imagens[] file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
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
			'name'				=> 'required|string|max:191',
			'text'				=> 'required|string|max:10000',
			'sinopse'			=> 'sometimes|string|max:191',
            'imagens'			=> 'sometimes|nullable|array|min:1',
            'imagens.*'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			// 'legenda'			=> 'sometimes|nullable|array',
			// 'legenda.*'			=> 'sometimes|nullable|string|max:191',
			'order'				=> 'required|integer',
			'is_active'			=> 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$vale_rio_doce       = new ValeRioDoce($request->only(['name', 'text', 'order', 'sinopse', 'is_active']));
			$vale_rio_doce->uuid = uuid();
			$vale_rio_doce->save();
			// $legendas = $request->input('legenda');

			// sobe as midias
			if ($request->hasFile('imagens'))
            {
                foreach ($request->file('imagens') as $key => $file)
                {
                    $file_name = $vale_rio_doce->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
                    $file_path = storage_path('app/public/valeriodoce/' . $file_name);
                    $file_url  = 'storage/valeriodoce/' . $file_name;

                    // Save image
                    Image::make($file)->fit(1000, 1000)->save($file_path);

                    $vale_rio_doce->media()->create([
                        'uuid'        => uuid(),
                        'type'        => 'image',
                        'file'        => $file_url,
                        'name'        => $file->getClientOriginalName(),
                        // 'description' => $legendas[$key] ?? null,
                        'mime'        => $file->getClientMimeType(),
                        'size'        => $file->getSize(),
                        'width'       => 1000,
                        'height'      => 1000,
                        'order'       => $key,
                        'is_active'   => true,
                    ]);
                }
			}
			
			$vale_rio_doce->save();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $vale_rio_doce->uuid, 'Criou um novo conteudo vale do rio doce', $vale_rio_doce);

		return (new ValeRioDoceResource($vale_rio_doce))->response()->setStatusCode(201);
	}

	/**
	 * Get vale do rio doce
	 *
	 * @permission vale_rio_doce.show
	 *
	 * @param ValeRioDoce $vale_rio_doce
	 *
	 * @return ValeRioDoceResource
	 */
	public function show(ValeRioDoce $vale_rio_doce)
	{
		$vale_rio_doce->load(['media']);

		return new ValeRioDoceResource($vale_rio_doce);
	}

	/**
	 * Update vale do rio doce
	 *
	 * @permission vale_rio_doce.edit
	 *
	 * @param Request $request
	 * @param ValeRioDoce     $vale_rio_doce
	 *
	 * @bodyParam  name string nullable|string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 realText(2000)
	 * @bodyParam  sinopse string sometimes|nullable|string|max:191 string
	 * @bodyParam  imagens[] file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  imagens_no_delete[] array sometimes|nullable|array|max:191 uuid
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  order integer sometimes|nullable|integer integer
	 *
	 * @return ValeRioDoceResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, ValeRioDoce $vale_rio_doce)
	{
		$this->validate($request, [
			'name'				=> 'sometimes|nullable|string|max:191',
			'text'				=> 'sometimes|nullable|string|max:10000',
			'sinopse'				=> 'sometimes|string|max:191',
            'imagens'			=> 'sometimes|nullable|array',
            'imagens.*'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			// 'legenda'			=> 'sometimes|nullable|array',
			// 'legenda.*'			=> 'sometimes|nullable|string|max:191',
			'order'				=> 'sometimes|nullable|integer',
			'is_active'			=> 'sometimes|nullable|boolean',
			'imagens_no_delete' => 'sometimes|nullable|array',
			'imagens_no_delete.*' => 'sometimes|nullable|string|max:191',

		]);

		$vale_rio_doce_old = clone $vale_rio_doce;

		// $legendas = $request->input('legenda');
		$medias_no_delete = collect($request->input('imagens_no_delete'))->unique()->all();

		DB::beginTransaction();

		try
		{
			// Update
			$vale_rio_doce->fill($request->only(['name', 'text', 'sinopse', 'video', 'order', 'is_active']));
			$vale_rio_doce->save();

			// antes de adicionar imagens novas, verifica se tem imagens para excluir
			// faz um foreach pelas imagens existentes, se existir diferença de uuid no array que veio, entao exclui o que NÃO VEIO
			$medias_exist = $vale_rio_doce->media()->get();

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
				$order = $vale_rio_doce->media()->max('order') ?? 0;

				foreach( $request->file('imagens') as $index => $file )
				{
					$file_name   = uuid() . '.jpg';
					$file_folder = 'storage/valeriodoce/';
					$file_path   = public_path($file_folder) . $file_name;
					$file_url    = $file_folder . $file_name;

					// Save image
					$image_instance = Image::make($file)->resize(1000, 1000, function ($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					})->save($file_path);

					$vale_rio_doce->media()->create([
						'uuid'			=> uuid(),
						'type'			=> 'image',
						'file'			=> $file_url,
						'name'			=> $file->getClientOriginalName(),
						// 'description'	=> $legendas[$key] ?? null,
						'width'			=> $image_instance->width(),
						'height'		=> $image_instance->height(),
						'order'			=> $order + $index,
					]);
				}
			}

			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'valeriodoce';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($vale_rio_doce->capa)
				{
					unlink($vale_rio_doce->capa);
				}

				$vale_rio_doce->update([
					'capa' => $path
				]);
			}

			if ($request->hasFile('icone'))
            {
				$file = $request->file('icone');
				$path = 'valeriodoce';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($vale_rio_doce->icone)
				{
					unlink($vale_rio_doce->icone);
				}

				$vale_rio_doce->update([
					'icone' => $path
				]);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $vale_rio_doce->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $vale_rio_doce_old->name, 'Editou o conteudo vale do rio doce', $vale_rio_doce, $vale_rio_doce_old);
		}

		return new ValeRioDoceResource($vale_rio_doce);
	}


	/**
	 * Remove vale do rio doce
	 *
	 * @permission vale_rio_doce.delete
	 *
	 * @param ValeRioDoce $vale_rio_doce
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(ValeRioDoce $vale_rio_doce)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$vale_rio_doce->media()->delete();
			$vale_rio_doce->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $vale_rio_doce->name, 'Deletou um conteudo vale do rio doce', $vale_rio_doce);

		return response(null, 204);
	}

	/**
	 * Export vale do rio doce
	 *
	 * @permission vale_rio_doce.export
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

		return $this->exportData($columns, $items, 'valeriodoce', 'Exportou conteudo vale do rio doce');
	}
}
