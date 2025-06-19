<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\RepositoryException;
use App\Http\Resources\Admin\RoleResource;
use App\Models\Permission;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Class RolesController
 *
 * @resource Roles
 * @package  App\Http\Controllers\v1\Admin
 */
class RolesController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'name',
		'description',
		'is_system',
		'created_at',
		'updated_at',
		'permissions_count',
		'users_count',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
		'description',
	];

	/**
	 * Get all roles
	 *
	 * @permission roles.list
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `description` `is_system` `created_at` `updated_at` `permissions_count` `users_count`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `description`
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
			'created_at'   => 'sometimes|array',
			'created_at.*' => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

		$items = Role::query();

		// Load relations
		$items->with('permissions');

		// Count relations
		$items->withCount(['permissions', 'users']);

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
			});
		}

		foreach( $orderBy as $orderByField )
		{
			$items->orderBy($orderByField['name'], $orderByField['sort']);
		}

		// Bugfix orderBy with field withCount
		if( $this->hasOrderBy($orderBy, 'permissions_count', 'users_count') )
		{
			$items->orderBy('id', 'asc');
		}

		// Only get data to export
		if( $this->getExport )
		{
			return $items->get();
		}

		$items = $items->paginate($limit)->appends($request->except('page'));

		return RoleResource::collection($items);
	}

	/**
	 * Create role
	 *
	 * @permission roles.create
	 *
	 * @param Request $request
	 *
	 * @bodyParam  name string required|string|max:50 static(Manager)
	 * @bodyParam  description string required|string|max:100 text(100)
	 * @bodyParam  permissions[] array required|array|min:1 static()
	 * @bodyParam  permissions[0] uuid required|uuid uuid
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name'          => 'required|string|max:50',
			'description'   => 'required|string|max:100',
			'permissions'   => 'required|array|min:1',
			'permissions.*' => 'required|uuid',
		]);

		$permissions = Permission::whereIn('uuid', collect($request->input('permissions'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

		if( !count($permissions) )
		{
			throw ValidationException::withMessages(['permissions' => __('errors.repository.invalid_permissions_selected')]);
		}

		DB::beginTransaction();

		try
		{
			// Create
			$role       = new Role($request->only(['name', 'description']));
			$role->uuid = uuid();
			$role->save();

			$role->permissions()->sync($permissions);

			DB::commit();
		}
		catch( Throwable $e )
		{
			DB::rollBack();

			throw $e;
		}

		// Load relations
		$role->load('permissions');

		// Count relations
		$role->loadCount(['permissions', 'users']);

		// Save log
		$this->log(__FUNCTION__, $role->name, 'Criou um novo papél', $role);

		return (new RoleResource($role))->response()->setStatusCode(201);
	}

	/**
	 * Get role
	 *
	 * @permission roles.show
	 *
	 * @param Role $role
	 *
	 * @return RoleResource
	 */
	public function show(Role $role)
	{
		// Load relations
		$role->load('permissions');

		// Count relations
		$role->loadCount(['permissions', 'users']);

		return new RoleResource($role);
	}

	/**
	 * Update role
	 *
	 * @permission roles.edit
	 *
	 * @param Request $request
	 * @param Role    $role
	 *
	 * @bodyParam  name string string|max:50 string
	 * @bodyParam  description string required|string|max:100 text(100)
	 * @bodyParam  permissions[] array array|min:1 static()
	 * @bodyParam  permissions[0] uuid required|uuid uuid
	 *
	 * @return RoleResource
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Throwable
	 */
	public function update(Request $request, Role $role)
	{
		// Can not delete system
		if( $role->is_system )
		{
			throw RepositoryException::make('edit_system', ['uuid' => $role->uuid]);
		}

		$this->validate($request, [
			'name'          => 'sometimes|required|string|max:50',
			'description'   => 'sometimes|required|string|max:100',
			'permissions'   => 'sometimes|required|array|min:1',
			'permissions.*' => 'required|uuid',
		]);

		$role_old = clone $role;

		// Load relations
		$role_old->load('permissions');

		// Count relations
		$role_old->loadCount(['permissions', 'users']);

		DB::beginTransaction();

		try
		{
			// Update
			$role->update($request->only(['name', 'description']));

			if( $request->has('permissions') )
			{
				$permissions = Permission::whereIn('uuid', collect($request->input('permissions'))->unique()->all())->orderBy('id', 'asc')->pluck('id');

				if( !count($permissions) )
				{
					throw RepositoryException::make('invalid_permissions_selected');
				}

				$permissions_sync = $role->permissions()->sync($permissions);

				if( count($permissions_sync['attached']) || count($permissions_sync['detached']) || count($permissions_sync['updated']) )
				{
					if( !$role->wasChanged() )
					{
						// Update timestamp
						$role->touch();
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
		$role->load('permissions');

		// Count relations
		$role->loadCount(['permissions', 'users']);

		if( $role->wasChanged() )
		{
			// Save log
			$this->log(__FUNCTION__, $role_old->name, 'Editou um papél', $role, $role_old);
		}

		return new RoleResource($role);
	}

	/**
	 * Remove role
	 *
	 * @permission roles.delete
	 *
	 * @param Role $role
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws \Exception
	 */
	public function destroy(Role $role)
	{
		// Can not delete system
		if( $role->is_system )
		{
			throw RepositoryException::make('delete_system', ['uuid' => $role->uuid]);
		}

		// It can not be excluded if have users
		if( $role->users()->count() )
		{
			throw RepositoryException::make('delete_role_with_users', ['uuid' => $role->uuid]);
		}

		// Delete
		$role->delete();

		// Save log
		$this->log(__FUNCTION__, $role->name, 'Deletou um papél', $role);

		return response(null, 204);
	}

	/**
	 * Autocomplete roles
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `description` `is_system` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `description`
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function autocomplete(Request $request)
	{
		$this->validate($request, [
			'orderBy' => 'sometimes|nullable|string',
			'search'  => 'sometimes|nullable|string',
		]);

		$this->fieldSortable = [
			'id',
			'name',
			'description',
			'is_system',
			'created_at',
			'updated_at',
		];

		$this->fieldSearchable = [
			'uuid',
			'name',
			'description',
		];

		$items = Role::query();

		$search  = $request->input('search');
		$orderBy = $this->getOrderBy($request->input('orderBy'));

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

		$items = $items->get();

		return RoleResource::collection($items);
	}

	/**
	 * Export roles
	 *
	 * @permission roles.export
	 *
	 * @param Request $request
	 *
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `description` `is_system` `created_at` `updated_at` `permissions_count` `users_count`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `name` `description`
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
				'value' => 'description',
			],
			[
				'name'  => 'Total de permissões',
				'value' => 'permissions_count',
			],
			[
				'name'  => 'Total de usuários',
				'value' => 'users_count',
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

		return $this->exportData($columns, $items, 'roles', 'Exportou papéis');
	}
}
