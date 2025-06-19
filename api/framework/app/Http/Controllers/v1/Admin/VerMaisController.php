<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Resources\Admin\VerMaisResource;
use App\Models\VerMais;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class VerMaisController
 *
 * @resource Conteudo ver Mais
 * @package  App\Http\Controllers\v1\Admin
 */
class VerMaisController extends Controller
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
	 * Get all ver mais
	 *
	 * @permission ver_mais.list
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

		$items = VerMais::query();

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

		return VerMaisResource::collection($items);
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
			'next_order' => (VerMais::orderBy('order', 'desc')->first(['order'])->order ?? 0) + 1,
		];
	}

	/**
	 * Create ver mais
	 *
	 * @permission ver_mais.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  text string required|string|max:10000 realText(2000)
	 * @bodyParam  icone file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  legenda[] string sometimes|nullable|string|max:191 string
	 * @bodyParam  video string required|string|max:191 string
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
			'video'				=> 'sometimes|string|max:191',
			'capa'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'icone'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
            'imagens'			=> 'sometimes|nullable|array',
            'imagens.*'			=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'legenda'			=> 'sometimes|nullable|array',
			'legenda.*'			=> 'sometimes|nullable|string|max:191',
			'order'				=> 'required|integer',
			'is_active'			=> 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
			// Create
			$ver_mais       = new VerMais($request->only(['name', 'text', 'order', 'video', 'is_active']));
			$ver_mais->uuid = uuid();
			$ver_mais->save();
			$legendas = $request->input('legenda');

			// sobe as midias
			if ($request->hasFile('imagens'))
            {
                foreach ($request->file('imagens') as $key => $file)
                {
                    $file_name = $ver_mais->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
                    $file_path = storage_path('app/public/vermais/' . $file_name);
                    $file_url  = 'storage/vermais/' . $file_name;

                    // Save image
                    Image::make($file)->fit(1000, 1000)->save($file_path);

                    $ver_mais->media()->create([
                        'uuid'        => uuid(),
                        'type'        => 'image',
                        'file'        => $file_url,
                        'name'        => $file->getClientOriginalName(),
                        'description' => $legendas[$key] ?? null,
                        'mime'        => $file->getClientMimeType(),
                        'size'        => $file->getSize(),
                        'width'       => 1000,
                        'height'      => 1000,
                        'order'       => $key,
                        'is_active'   => true,
                    ]);
                }
			}
			
			$ver_mais->save();

			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'vermais';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($ver_mais->capa)
				{
					unlink($ver_mais->capa);
				}

				$ver_mais->update([
					'capa' => $path
				]);
			}

			if ($request->hasFile('icone'))
            {
				$file = $request->file('icone');
				$path = 'vermais';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($ver_mais->icone)
				{
					unlink($ver_mais->icone);
				}

				$ver_mais->update([
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

		// Save log
		$this->log(__FUNCTION__, $ver_mais->uuid, 'Criou um novo conteudo ver mais', $ver_mais);

		return (new VerMaisResource($ver_mais))->response()->setStatusCode(201);
	}

	/**
	 * Get ver mais
	 *
	 * @permission ver_mais.show
	 *
	 * @param VerMais $ver_mais
	 *
	 * @return VerMaisResource
	 */
	public function show(VerMais $ver_mais)
	{
		$ver_mais->load(['media']);

		return new VerMaisResource($ver_mais);
	}

	/**
	 * Update ver mais
	 *
	 * @permission ver_mais.edit
	 *
	 * @param Request $request
	 * @param VerMais     $ver_mais
	 *
	 * @bodyParam  name string nullable|string|max:191 string
	 * @bodyParam  text string nullable|string|max:10000 realText(2000)
	 * @bodyParam  icone file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  delete_icone boolean boolean boolean Default false
	 * @bodyParam  capa file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  delete_capa boolean boolean boolean Default false
	 * @bodyParam  video string sometimes|nullable|string|max:191 string
	 * @bodyParam  imagens[] file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  legenda[] string sometimes|nullable|string|max:191 string
	 * @bodyParam  imagens_no_delete[] array sometimes|nullable|array|max:191 uuid
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  order integer sometimes|nullable|integer integer
	 * 
	 * @bodyParam  imagens_update[] array required|array uuid
	 * @bodyParam  legenda_update[] string sometimes|nullable|string|max:191 string
	 *
	 * @return VerMaisResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, VerMais $ver_mais)
	{
		$this->validate($request, [
			'name'					=> 'sometimes|nullable|string|max:191',
			'text'					=> 'sometimes|nullable|string|max:10000',
			'video'					=> 'sometimes|string|max:191',
			'capa'					=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'delete_capa'			=> 'sometimes|nullable|boolean',
			'icone'					=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'delete_icone'			=> 'sometimes|nullable|boolean',
            'imagens'				=> 'sometimes|nullable|array',
            'imagens.*'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'legenda'				=> 'sometimes|nullable|array',
			'legenda.*'				=> 'sometimes|nullable|string|max:191',
			'order'					=> 'sometimes|nullable|integer',
			'is_active'				=> 'sometimes|nullable|boolean',
			'imagens_no_delete'		=> 'sometimes|nullable|array',
			'imagens_no_delete.*'	=> 'sometimes|nullable|string|max:191',

            'imagens_update'		=> 'sometimes|nullable|array',
            'imagens_update.*'		=> 'sometimes|nullable|uuid|max:191',
			'legenda_update'		=> 'sometimes|nullable|array',
			'legenda_update.*'		=> 'sometimes|nullable|string|max:191',
		]);

		$ver_mais_old = clone $ver_mais;

		$legendas = $request->input('legenda');
		$legendas_update = $request->input('legenda_update');
		$medias_no_delete = collect($request->input('imagens_no_delete'))->unique()->all();
        $delete_icone = (bool)$request->input('delete_icone');
        $delete_capa = (bool)$request->input('delete_capa');

		DB::beginTransaction();

		try
		{
			// Update
			$ver_mais->fill($request->only(['name', 'text', 'video', 'order', 'is_active']));
			$ver_mais->save();

			// antes de adicionar imagens novas, verifica se tem imagens para excluir
			// faz um foreach pelas imagens existentes, se existir diferença de uuid no array que veio, entao exclui o que NÃO VEIO
			$medias_exist = $ver_mais->media()->get();

			foreach ($medias_exist as $key => $media_db)
			{
				if(!in_array($media_db->uuid, $medias_no_delete))
				{
					$media_db->delete();
				}
			}

			// update legendas
			if ($request->has('imagens_update'))
            {
				foreach( $request->input('imagens_update') as $key => $uuid )
				{
					$media = $ver_mais->media()->where('uuid', $uuid)->first();

					if( $media )
					{
						$media->description = $legendas_update[$key] ?? null;
						$media->save();
					}
				}
			}
		
			// add
			if ($request->hasFile('imagens'))
            {
				$order = $ver_mais->media()->max('order') ?? 0;

				foreach( $request->file('imagens') as $key => $file )
				{
					$file_name   = uuid() . '.jpg';
					$file_folder = 'storage/vermais/';
					$file_path   = public_path($file_folder) . $file_name;
					$file_url    = $file_folder . $file_name;

					// Save image
					$image_instance = Image::make($file)->resize(1000, 1000, function ($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					})->save($file_path);

					$ver_mais->media()->create([
						'uuid'			=> uuid(),
						'type'			=> 'image',
						'file'			=> $file_url,
						'name'			=> $file->getClientOriginalName(),
						'description'	=> $legendas[$key] ?? null,
						'width'			=> $image_instance->width(),
						'height'		=> $image_instance->height(),
						'order'			=> $order + $key,
					]);
				}
			}

			if ($request->hasFile('capa'))
            {
				$file = $request->file('capa');
				$path = 'vermais';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($ver_mais->capa)
				{
					unlink($ver_mais->capa);
				}

				$ver_mais->update([
					'capa' => $path
				]);
			}

            if($delete_capa)
            {
                // Delete file
                if( $ver_mais->capa )
                {
                    $file_path = public_path($ver_mais->capa);
                    if( file_exists($file_path) )
                    {
                        unlink($ver_mais->capa);
                    }
                }

                $ver_mais->capa = null;
				$ver_mais->save();
            }

			if ($request->hasFile('icone'))
            {
				$file = $request->file('icone');
				$path = 'vermais';
				$name = uuid().'.'.$file->getClientOriginalExtension();
				$path = 'storage/'.$file->storeAs($path, $name);

				// exclui a antiga se existir
				if($ver_mais->icone)
				{
					unlink($ver_mais->icone);
				}

				$ver_mais->update([
					'icone' => $path
				]);
			}

            if($delete_icone)
            {
                // Delete file
                if( $ver_mais->icone )
                {
                    $file_path = public_path($ver_mais->icone);
                    if( file_exists($file_path) )
                    {
                        unlink($ver_mais->icone);
                    }
                }

                $ver_mais->icone = null;
				$ver_mais->save();
            }

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $ver_mais->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $ver_mais_old->name, 'Editou o conteudo ver mais', $ver_mais, $ver_mais_old);
		}

		return new VerMaisResource($ver_mais);
	}

	/**
	 * Remove ver mais
	 *
	 * @permission ver_mais.delete
	 *
	 * @param VerMais $ver_mais
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(VerMais $ver_mais)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$ver_mais->media()->delete();
			$ver_mais->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $ver_mais->name, 'Deletou um conteudo ver mais', $ver_mais);

		return response(null, 204);
	}

	/**
	 * Export ver mais
	 *
	 * @permission ver_mais.export
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

		return $this->exportData($columns, $items, 'vermais', 'Exportou conteudo ver mais');
	}
}
