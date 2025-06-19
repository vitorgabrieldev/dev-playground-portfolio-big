<?php

namespace App\Http\Controllers\v1\Admin;

use App\Enum\Document;
use App\Http\Resources\Admin\CustomerResource;
// use App\Lib\Zenvia;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use App\Models\PerfisAcesso;
use App\Models\Preferencias;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class CustomerController
 *
 * @resource Cliente
 * @package  App\Http\Controllers\v1\Admin
 */
class CustomerController extends Controller
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
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
        'email',
		'document',
		'cnpj'
	];

	/**
	 * Get all clientes
	 *
	 * @permission customers.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   perfil_acesso_id string string|max:191 uuid
	 * @urlParam   preferencias[] array required|string|max:191 uuid
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `is_active` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `email` `document` `cnpj`
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
			'perfil_acesso_id'	=> 'sometimes|nullable|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'is_active'    => 'sometimes|integer',
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
			'preferencias' => 'sometimes|array',
			'preferencias.*' => 'sometimes|string|exists:preferencias,uuid',
		]);

		$items = Customer::query();

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$perfil_acesso_id  = $request->input('perfil_acesso_id');
		$limit            = $this->getLimit($request->input('limit'));
		$is_active        = $request->input('is_active');
		$created_at_start = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end   = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;
		$preferencias	= $request->input('preferencias');
    
		// Filtro por preferências
		if ($preferencias && count($preferencias)) {
			$preferenciasid = Preferencias::whereIn('uuid', collect($preferencias)->unique()->all())->orderBy('id', 'asc')->pluck('id');

			$items->whereRelation('preferencias', function($query) use ($preferenciasid) {
				$query->whereIn('preferencia_id', $preferenciasid);
			});
		}

		if($perfil_acesso_id)
		{
			$categoria = PerfisAcesso::where("uuid", $perfil_acesso_id)->first(["id","name"]) ?? null;
			if(!$categoria)
			{
				throw ValidationException::withMessages(['perfil_acesso_id' => __('Perfil não encontrado. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
			}

			$items->whereRelation('perfil', 'id', $categoria->id);
		}
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

		return CustomerResource::collection($items);
	}

	/**
	 * Get all clientes deletedos
	 *
	 * @permission customers-deleted.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   perfil_acesso_id string string|max:191 uuid
	 * @urlParam   preferencias[] array required|string|max:191 uuid
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `is_active` `created_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `email` `document` `cnpj`
	 * @urlParam   is_active integer integer static(1) Available values: `0` `1`
	 * @urlParam   deleted_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   deleted_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
	 *
	 * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function indexDeleted(Request $request)
	{
		$this->validate($request, [
			'limit'        => 'sometimes|nullable|integer',
			'page'         => 'sometimes|nullable|integer',
			'perfil_acesso_id'	=> 'sometimes|nullable|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'orderBy'      => 'sometimes|nullable|string',
			'search'       => 'sometimes|nullable|string',
			'is_active'    => 'sometimes|integer',
			'deleted_at'   => 'sometimes|array',
			'deleted_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
			'preferencias' => 'sometimes|array',
			'preferencias.*' => 'sometimes|string|exists:preferencias,uuid',
		]);

		$items = Customer::query()->onlyTrashed();

		$search           = $request->input('search');
		$orderBy          = $this->getOrderBy($request->input('orderBy'));
		$limit            = $this->getLimit($request->input('limit'));
		$is_active        = $request->input('is_active');
		$deleted_at_start = $request->has('deleted_at.0') ? Carbon::parse($request->input('deleted_at.0'))->setTimezone('UTC') : null;
		$deleted_at_end   = $request->has('deleted_at.1') ? Carbon::parse($request->input('deleted_at.1'))->setTimezone('UTC') : null;
		$preferencias = $request->input('preferencias');
    
		// Filtro por preferências
		if ($preferencias && count($preferencias)) {
			$preferenciasid = Preferencias::whereIn('uuid', collect($preferencias)->unique()->all())->orderBy('id', 'asc')->pluck('id');

			$items->whereHas('preferencias', function($query) use ($preferenciasid) {
				$query->whereIn('preferencias.id', $preferenciasid);
			}, '=', count($preferenciasid));
		}

		// is_active
		if( $is_active !== null )
		{
			$items->where('is_active', '=', $is_active ? 1 : 0);
		}

		// created_at
		if( $deleted_at_start )
		{
			$items->where('deleted_at', '>=', $deleted_at_start);
		}
		if( $deleted_at_end )
		{
			$items->where('deleted_at', '<=', $deleted_at_end);
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

		return CustomerResource::collection($items);
	}

	/**
	 * Create cliente
	 *
	 * @permission customers.create
	 *
	 * @param Request $request
	 * @bodyParam  password string required|string|min:6 password
	 * @bodyParam  password_confirmation string required|string|min:6 static(my_new_password) Confirmation, must equal `password`
	 * @bodyParam  name string required|string|max:191 string
	 * @bodyParam  document string sometimes|nullable|string|max:20 string static(000.999.777-11)
	 * @bodyParam  cnpj string sometimes|nullable|string|max:20 string static(16.159.151/0001-27)
     * @bodyParam  email email required|string|email|max:191 email
     * @bodyParam  phone string required|string|email|max:18 phone static((43) 91111-2222)
	 * @bodyParam  is_active boolean sometimes|nullable|boolean boolean
	 * @bodyParam  avatar file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  birth_date string string static(1999-07-29) ISO 8601 `Y-m-d` data de nascimento
	 * @bodyParam  preferencias[] array required|string|max:191 uuid
	 * @bodyParam  perfil_acesso uuid required|uuid uuid
	 * @bodyParam  accept_newsletter boolean boolean boolean Default true
	 *
	 * @return CustomerResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name'					=> 'required|string|max:191',
			'email'					=> 'required|string|unique:customers|email|max:191',
            'phone'					=> 'required|string|phone|max:18',
			'document'				=> 'sometimes|nullable|string|max:20',
			'cnpj'					=> 'sometimes|nullable|string|max:20',
			'password'				=> 'required|string|min:6',
			'password_confirmation' => 'required|string|min:6|same:password',
			'is_active'				=> 'sometimes|nullable|boolean',
			'avatar'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'birth_date'			=> 'sometimes|date_format:Y-m-d',
			'preferencias'			=> 'sometimes|array|min:1',
			'preferencias.*'		=> 'sometimes|string|max:191',
			'perfil_acesso'			=> 'required|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'accept_newsletter'		=> 'sometimes|required|boolean',
		]);

		DB::beginTransaction();

		try
		{
            // verifica o tipo de documento
            if(identificarDocumento($request->input('document')) == Document::DESCONHECIDO)
            {
                return response()->json(['document' => 'Tipo de documento inválido. Para prosseguir com a criação da conta por favor adicione um documento válido. Caso persista, entre em contato com o suporte.'],422);
            }

            if(Customer::where("document",$request->input('document'))->exists())
            {
                return response()->json(['document' => 'Já existe uma conta com este documento. Para prosseguir com a criação da conta por favor adicione um outro documento. Caso persista, entre em contato com o suporte.'],422);
            }

            if(identificarDocumento($request->input('document')) == Document::CPF)
            {
                // valida documento
                if(!validarCPF($request->input('document')))
                {
                    return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CPF válido. Caso persista, entre em contato com o suporte.'],422);
                }
            }
            else
            {
                // valida documento
                if(!validarCNPJ($request->input('document')))
                {
                    return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
                }
            }

            if(identificarDocumento($request->input('cnpj')) == Document::DESCONHECIDO)
            {
                return response()->json(['cnpj' => 'Tipo de documento inválido. Para prosseguir com a criação da conta por favor adicione um documento válido. Caso persista, entre em contato com o suporte.'],422);
            }
			else
            if(identificarDocumento($request->input('cnpj')) == Document::CNPJ)
            {
                // valida documento
                if(!validarCNPJ($request->input('document')))
                {
                    return response()->json(['cnpj' => 'CNPJ inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
                }
			}

			$preferencias = Preferencias::whereIn('uuid', collect($request->input('preferencias'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

			if( !count($preferencias) )
			{
				throw ValidationException::withMessages(['preferencias' => __('Preferencia selecionada inválida. Tente novamente ou entre em contato com o suporte.')]);
			}
	
			if($request->has('perfil_acesso'))
			{
				$perfil = PerfisAcesso::where("uuid", $request->input('perfil_acesso'))->first(["id"]) ?? null;
				if(!$perfil)
				{
					throw ValidationException::withMessages(['perfil_acesso' => __('Perfil não encontrado. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}
			}

			// Create
			$customer = new Customer($request->only([
				'name',
                'phone',
				'birth_date',
				'document',
				'cnpj',
				'email',
				'accept_newsletter',
				'is_active',
			]));

            $customer->uuid = uuid();
            $customer->accepted_term_of_users_at = now();
            $customer->accepted_policy_privacy_at = now();
			$customer->perfil_acesso_id = $perfil->id;

			if($request->input('password'))
			{
				$customer->forceFill([
					'password' => Hash::make($request->input('password')),
				])->save();

				$customer->setRememberToken(Str::random(10));
			}

			if ($request->hasFile('avatar'))
            {
				$file = $request->file('avatar');
				$file_name = $customer->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
				$file_path = storage_path('app/public/avatar/' . $file_name);
				$file_url  = 'storage/avatar/' . $file_name;

				// Save image
				Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);

				//
				$customer->avatar = $file_url;
			}

			$customer->save();

			$customer->preferencias()->sync($preferencias);

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $customer->document, 'Criou um cliente', $customer);

		return (new CustomerResource($customer))->response()->setStatusCode(201);
	}

	/**
	 * Update cliente
	 *
	 * @permission customers.edit
	 *
	 * @param Request $request
	 * @param Customer $customer
	 *
	 * @bodyParam  password string sometimes|nullable|string|min:6 password
	 * @bodyParam  password_confirmation string required|string|min:6 static(my_new_password) Confirmation, must equal `password`
	 * @bodyParam  name string sometimes|nullable|string|max:191 string
	 * @bodyParam  document string sometimes|nullable|string|max:20 string static(000.999.777-11)
	 * @bodyParam  cnpj string sometimes|nullable|string|max:20 string static(16.159.151/0001-27)
     * @bodyParam  email email sometimes|nullable|string|email|max:191 email
     * @bodyParam  phone string sometimes|nullable|string|email|max:18 phone static((43) 91111-2222)
	 * @bodyParam  is_active boolean sometimes|nullable|boolean boolean
	 * @bodyParam  avatar file sometimes|nullable|mimes:jpg,jpeg,png|max:4mb file
	 * @bodyParam  birth_date string string static(1999-07-29) ISO 8601 `Y-m-d` data de nascimento
	 * @bodyParam  preferencias[] array required|string|max:191 uuid
	 * @bodyParam  perfil_acesso uuid required|uuid uuid
	 * @bodyParam  accept_newsletter boolean boolean boolean Default true
	 * @bodyParam  notify_general boolean boolean boolean Default true
	 *
	 * @return CustomerResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, Customer $customer)
	{
		$this->validate($request, [
			'name'					=> 'sometimes|nullable|string|max:191',
            'phone'					=> 'sometimes|nullable|string|phone|max:18',
			'document'				=> 'sometimes|nullable|string|max:20',
			'cnpj'					=> 'sometimes|nullable|string|max:20',
			'email'					=> [
				'sometimes',
				'nullable',
				'email',
				'max:191',
				Rule::unique((new Customer())->getTable(), 'email')->where(function ($query) {
					return $query->whereNull('deleted_at');
				})->ignore($customer->id),
			],
			'password'				=> 'sometimes|nullable|string|min:6',
			'password_confirmation' => 'sometimes|nullable|string|min:6|same:password',
			'is_active'				=> 'sometimes|nullable|boolean',
			'avatar'				=> 'sometimes|nullable|mimes:jpg,jpeg,png|max:4000',
			'birth_date'			=> 'sometimes|date_format:Y-m-d',
			'preferencias'			=> 'sometimes|array|min:1',
			'preferencias.*'		=> 'sometimes|string|max:191',
			'perfil_acesso'			=> 'required|string|exists:' . (new PerfisAcesso())->getTable() . ',uuid',
			'accept_newsletter'		=> 'sometimes|required|boolean',
			'notify_general'		=> 'sometimes|required|boolean',
		]);

		$cusstomer_old = clone $customer;

		DB::beginTransaction();

		try
		{
            if($request->has('document'))
            {
                if(identificarDocumento($request->input('document')) == Document::CPF)
                {
                    // valida documento
                    if(!validarCPF($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CPF válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }
                else
                {
                    // valida documento
                    if(!validarCNPJ($request->input('document')))
                    {
                        return response()->json(['document' => 'Documento inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
                    }
                }

                if(Customer::where("document",$request->input('document'))->where('id',"!=",$customer->id)->exists())
                {
                    return response()->json(['document' => 'Já existe uma conta com este documento. Para prosseguir com a criação da conta por favor adicione um outro documento. Caso persista, entre em contato com o suporte.'],422);
                }
            }

			if($request->has('cnpj'))
			{
				if(identificarDocumento($request->input('cnpj')) == Document::DESCONHECIDO)
				{
					return response()->json(['cnpj' => 'Tipo de documento inválido. Para prosseguir com a criação da conta por favor adicione um documento válido. Caso persista, entre em contato com o suporte.'],422);
				}
				else
				if(identificarDocumento($request->input('cnpj')) == Document::CNPJ)
				{
					// valida documento
					if(!validarCNPJ($request->input('document')))
					{
						return response()->json(['cnpj' => 'CNPJ inválido. Para prosseguir com a criação da conta por favor adicione um CNPJ válido. Caso persista, entre em contato com o suporte.'],422);
					}
				}
			}

			$preferencias = Preferencias::whereIn('uuid', collect($request->input('preferencias'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

			if( !count($preferencias) )
			{
				throw ValidationException::withMessages(['preferencias' => __('Preferencia selecionada inválida. Tente novamente ou entre em contato com o suporte.')]);
			}
	
			if($request->has('perfil_acesso'))
			{
				$perfil = PerfisAcesso::where("uuid", $request->input('perfil_acesso'))->first(["id"]) ?? null;
				if(!$perfil)
				{
					throw ValidationException::withMessages(['perfil_acesso' => __('Perfil não encontrado. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
				}
			}

			// Update
			$customer->fill($request->only([
				'name',
                'phone',
				'birth_date',
				'document',
				'cnpj',
				'email',
				'accept_newsletter',
				'is_active',
				'notify_general',
			]));
			$customer->perfil_acesso_id = $perfil->id;

			if($request->input('password'))
			{
				$customer->forceFill([
					'password' => Hash::make($request->input('password')),
				])->save();

				$customer->setRememberToken(Str::random(10));
			}

			$customer->save();
			$customer->preferencias()->sync($preferencias);

			if($request->hasFile('avatar'))
			{
				$imagem_antiga = $customer->avatar;

				$file = $request->file('avatar');
				$file_name = $customer->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
				$file_path = storage_path('app/public/avatar/' . $file_name);
				$file_url  = 'storage/avatar/' . $file_name;

				// Save image
				Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);

				// Update
				$customer->update([
					'avatar' => $file_url,
				]);

				// remove a antiga imagem
				if($imagem_antiga)
				{
					File::delete($imagem_antiga);
				}
			}

			DB::commit();

			// Save log
			$this->log(__FUNCTION__, $cusstomer_old->name, 'Editou um cliente', $customer, $cusstomer_old);
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		return new CustomerResource($customer);
	}


	/**
	 * Change avatar cliente
	 *
     * @permission customers.edit
     *
	 * @param Request $request
	 *
	 * @bodyParam avatar file required|mimes:jpeg,png|max:2mb file
	 *
	 * @response {
	 * "file_url": "{url}"
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws ValidationException
	 */
	public function changeAvatar(Request $request, Customer $customer)
	{
		$this->validate($request, [
			'avatar' => 'required|mimes:jpeg,png|max:2000',
		]);

		// Get current user
		$cusstomer_old = clone $customer;

		DB::beginTransaction();

		try
		{
			$imagem_antiga = $customer->avatar;

			// sobe a nova imagem
			$file = $request->file('avatar');

            $file_name = $customer->uuid . '_' . uuid() . '.'.$file->getClientOriginalExtension();
            $file_path = storage_path('app/public/avatar/' . $file_name);
            $file_url  = 'storage/avatar/' . $file_name;

            // Save image
            Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);

            // Update
            $customer->update([
                'avatar' => $file_url,
            ]);

			// remove a antiga imagem
			if($imagem_antiga)
			{
				File::delete($imagem_antiga);
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		if( $customer->wasChanged() )
		{
            // Save log
            $this->log(__FUNCTION__, $cusstomer_old->nome, 'Alterou o avatar do cliente', $customer, $cusstomer_old);
		}

		return new CustomerResource($customer);
	}

	/**
	 * Remove cliente
	 *
	 * @permission customers.delete
	 *
	 * @param Customer $customer
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(Customer $customer)
	{
		DB::beginTransaction();

		try
		{
			// Delete
			$customer->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $customer->nome, 'Deletou um cliente', $customer);

		return response(null, 204);
	}

	/**
	 * Get cliente
	 *
	 * @permission customers.show
	 *
	 * @param Customer $customer
	 *
	 * @return CustomerResource
	 */
	public function show(Customer $customer)
	{
		$customer->load(['preferencias', 'perfil']);
		return (new CustomerResource($customer));
	}

	/**
	 * Get cliente deletado
	 *
	 * @permission customers-deleted.show
	 *
	 * @param string $uuid
	 *
	 * @return CustomerResource
	 */
	public function showDeleted(string $uuid)
	{
		$customer = Customer::with(['preferencias', 'perfil'])
        ->where("uuid", $uuid)
        ->withTrashed()
        ->first();

		if (!$customer) {
			abort(404);
		}

		return new CustomerResource($customer);
	}

	/**
	 * Autocomplete cliente
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
			'document',
			'cnpj',
		];

		$items = Customer::query();

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

		return CustomerResource::collection($items);
	}

	/**
	 * Export clientes
	 *
	 * @permission customers.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `is_active` `created_at`
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
				'name'  => 'E-mail',
				'value' => 'email',
			],
			[
				'name'  => 'Telefone',
				'value' => 'phone',
			],
			[
				'name'  => 'Documento',
				'value' => 'document',
			],
			[
				'name'  => 'CNPJ',
				'value' => 'cnpj',
			],
			[
				'name'  => 'Data de nascimento',
				'type'  => 'date',
				'value' => 'birth_date',
			],
			[
				'name'  => 'Avatar',
				'value' => function ($item) {
					return $item->avatar ? asset($item->avatar) : '';
				},
			],
			[
				'name'  => 'Aceita newsletter',
				'value' => function ($item) {
					return $item->accept_newsletter ? "sim" : "não";
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
				'name'  => 'Aceite dos termos de uso',
				'type'  => 'datetime',
				'value' => 'accepted_term_of_users_at',
            ],
			[
				'name'  => 'Aceite de politica de privacidade',
				'type'  => 'datetime',
				'value' => 'accepted_policy_privacy_at',
			],
			[
				'name'  => 'Última modificação',
				'type'  => 'datetime',
				'value' => 'updated_at',
			],
		];

		return $this->exportData($columns, $items, 'cliente', 'Exportou clientes');
	}

	/**
	 * Export clientes deletado
	 *
	 * @permission customers-deleted.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `is_active` `created_at`
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
	public function exportDeleted(Request $request)
	{
		$this->getExport = true;

		$items = $this->indexDeleted($request);

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
				'name'  => 'E-mail',
				'value' => 'email',
			],
			[
				'name'  => 'Telefone',
				'value' => 'phone',
			],
			[
				'name'  => 'Documento',
				'value' => 'document',
			],
			[
				'name'  => 'CNPJ',
				'value' => 'cnpj',
			],
			[
				'name'  => 'Data de nascimento',
				'type'  => 'date',
				'value' => 'birth_date',
			],
			[
				'name'  => 'Avatar',
				'value' => function ($item) {
					return $item->avatar ? asset($item->avatar) : '';
				},
			],
			[
				'name'  => 'Aceita newsletter',
				'value' => function ($item) {
					return $item->accept_newsletter ? "sim" : "não";
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
				'name'  => 'Aceite dos termos de uso',
				'type'  => 'datetime',
				'value' => 'accepted_term_of_users_at',
            ],
			[
				'name'  => 'Aceite de politica de privacidade',
				'type'  => 'datetime',
				'value' => 'accepted_policy_privacy_at',
			],
			[
				'name'  => 'Última modificação',
				'type'  => 'datetime',
				'value' => 'updated_at',
			],
		];

		return $this->exportData($columns, $items, 'cliente', 'Exportou clientes deletados');
	}


	/**
	 * active/deactive customer
	 *
	 * @permission customer.edit
	 *
	 * @param Request $request
	 * @param Customer    $customer
	 *
	 * @bodyParam  uuid string string uuid
	 * @bodyParam  is_active boolean boolean boolean
	 *
	 * @return CustomerResource
	 * @throws Throwable
	 * @throws \Illuminate\Validation\ValidationException
	 **/
	public function activeDisabled(Request $request, $customerUuid)
	{
		// Validação dos parâmetros recebidos
		$this->validate($request, [
			'is_active' => 'required|boolean',
		]);

		// Buscar o Customer usando o UUID
		$customer = Customer::where('uuid', $customerUuid)->first();

		if (!$customer) {
			return response()->json(['message' => 'Nenhum usuário encontrado.'], 404);
		}

		DB::beginTransaction();

		try {
			// Alterar o status de ativo/desativado do usuário
			$customer->is_active = $request->input('is_active');
			$customer->save();
			DB::commit();
		} catch (Throwable $e) {
			DB::rollBack();
			throw $e;
		}

		return response(null, 201);
	}
}
