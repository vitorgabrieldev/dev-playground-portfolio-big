<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\RepositoryException;
use App\Http\Resources\Admin\UserResource;
use App\Mail\Admin\PasswordMail;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Facades\Image;
use Throwable;

/**
 * Class UsersController
 *
 * @resource Users
 * @package  App\Http\Controllers\v1\Admin
 */
class UsersController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'name',
		'email',
		'avatar',
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
		'email',
	];

	/**
	 * Get all users
	 *
	 * @permission users.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `avatar` `is_active` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `email`
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

		$items = User::query();

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

		return UserResource::collection($items);
	}

	/**
	 * Create user
	 *
	 * @permission users.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  avatar string nullable|mimes:jpeg,png|max:4000 file
	 * @bodyParam  name string required|string|max:50 name
	 * @bodyParam  password_random boolean required|boolean boolean
	 * @bodyParam  password string required_if:password_random,0|string|min:6 static(123456)
	 * @bodyParam  password_confirmation string required_if:password_random,0|string|min:6 static(123456) Must contain the same value as `password`
	 * @bodyParam  email string required|string|email|max:100 safeEmail
	 * @bodyParam  is_active boolean boolean boolean Default true
	 * @bodyParam  roles[] array array|min:1 static()
	 * @bodyParam  roles[0] uuid required|uuid uuid
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'avatar'                => 'sometimes|nullable|mimes:jpeg,png|max:4000',
			'name'                  => 'required|string|max:50',
			'password_random'       => 'required|boolean',
			'password'              => 'required_if:password_random,0|nullable|string|min:6',
			'password_confirmation' => 'required_if:password_random,0|nullable|string|min:6|same:password',
			'email'                 => [
				'required',
				'string',
				'email',
				'max:100',
				Rule::unique((new User)->getTable(), 'email')->where(function ($query) {
					return $query->whereNull('deleted_at');
				}),
			],
			'is_active'             => 'sometimes|required|boolean',
			'roles'                 => 'required|array|min:1',
			'roles.*'               => 'required|uuid',
		]);

		$roles = Role::whereIn('uuid', collect($request->input('roles'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

		if( !count($roles) )
		{
			throw ValidationException::withMessages(['roles' => __('errors.repository.invalid_roles_selected')]);
		}

		DB::beginTransaction();

		try
		{
			$faker = Factory::create();

			$password = $request->input('password_random') ? $faker->regexify('[0-9]{8}') : $request->input('password');

			// Create
			$user           = new User($request->only(['name', 'email', 'is_active']));
			$user->uuid     = uuid();
			$user->password = bcrypt($password);

			// Avatar
			if( $request->hasFile('avatar') )
			{
				$file_name = uuid() . '.jpg';
				$file_path = storage_path('app/public/avatar/' . $file_name);
				$file_url  = 'storage/avatar/' . $file_name;

				// Save image
				Image::make($request->file('avatar'))->fit(170, 170)->save($file_path);

				$user->avatar = $file_url;
			}

			$user->save();

			$user->roles()->sync($roles);

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Load relations
		$user->load('roles.permissions');

		// Save log
		$this->log(__FUNCTION__, $user->name, 'Criou um novo usuário administrador', $user);

		// Send mail
		Mail::to($user->email)->queue(new PasswordMail($user, $password));

		return (new UserResource($user))->response()->setStatusCode(201);
	}

	/**
	 * Get user
	 *
	 * @permission users.show
	 *
	 * @param User $user
	 *
	 * @return UserResource
	 */
	public function show(User $user)
	{
		// Load relations
		$user->load('roles.permissions');

		return new UserResource($user);
	}

	/**
	 * Update user
	 *
	 * @permission users.edit
	 *
	 * @param Request $request
	 * @param User    $user
	 *
	 * @bodyParam  is_active boolean boolean boolean
	 * @bodyParam  roles[] array array|min:1 static()
	 * @bodyParam  roles[0] uuid required|uuid uuid
	 *
	 * @return UserResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, User $user)
	{
		// Get current user
		$userCurrent = $this->user();

		// Can not delete currrent user
		if( $user->id === $userCurrent->id )
		{
			throw RepositoryException::make('update_current_user', ['uuid' => $user->uuid]);
		}

		$this->validate($request, [
			'is_active' => 'sometimes|required|boolean',
			'roles'     => 'sometimes|required|array|min:1',
			'roles.*'   => 'required|uuid',
		]);

		$user_old = clone $user;

		// Load relations
		$user_old->load('roles.permissions');

		DB::beginTransaction();

		try
		{
			// Update
			$user->fill($request->only(['is_active']));

			$user->save();

			if( $request->has('roles') )
			{
				$roles = Role::whereIn('uuid', collect($request->input('roles'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

				if( !count($roles) )
				{
					throw ValidationException::withMessages(['roles' => __('errors.repository.invalid_roles_selected')]);
				}

				$roles_sync = $user->roles()->sync($roles);

				if( count($roles_sync['attached']) || count($roles_sync['detached']) || count($roles_sync['updated']) )
				{
					if( !$user->wasChanged() )
					{
						// Update timestamp
						$user->touch();
					}
				}
			}

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Load relations
		$user->load('roles.permissions');

		if( $user->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $user_old->name, 'Editou um usuário administrador', $user, $user_old);
		}

		return new UserResource($user);
	}

	/**
	 * Remove user
	 *
	 * @permission users.delete
	 *
	 * @param User $user
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
	public function destroy(User $user)
	{
		// Get current user
		$userCurrent = $this->user();

		// Can not delete currrent user
		if( $user->id === $userCurrent->id )
		{
			throw RepositoryException::make('delete_current_user', ['uuid' => $user->uuid]);
		}

		DB::beginTransaction();

		try
		{
			// Delete
			$user->delete();

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Save log
		$this->log(__FUNCTION__, $user->name, 'Deletou um usuário administrador', $user);

		return response(null, 204);
	}

	/**
	 * Autocomplete users
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

		$items = User::query();

		// Select
		$items->select('id', 'uuid', 'name', 'avatar', 'is_active', 'created_at', 'updated_at');

		$search    = $request->input('search');
		$orderBy   = $this->getOrderBy($request->input('orderBy'));
		$is_active = $request->input('is_active');

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

		$items = $items->limit(50)->get();

		return UserResource::collection($items);
	}

	/**
	 * Export users
	 *
	 * @permission users.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `email` `avatar` `is_active` `created_at` `updated_at`
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
				'name'  => 'Avatar',
				'value' => function ($item) {
					return $item->avatar ? asset($item->avatar) : '';
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

		return $this->exportData($columns, $items, 'users', 'Exportou usuários administradores');
	}
}
