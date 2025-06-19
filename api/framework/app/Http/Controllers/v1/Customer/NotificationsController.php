<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\NotificationsResource;
use App\Models\Notifications;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


/**
 * Class NotificationsController
 *
 * @resource Notifications
 * @package  App\Http\Controllers\v1\Customer
 */
class NotificationsController extends Controller
{

	/**
	 * Fields available for ordering
	 *
	 * @var array
	 */
	public $fieldSortable = [
		'id',
		'text',
		'data_envio',
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'text',
        'descricao',
	];

	/**
	 * Get all notifications
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   read integer integer static(1) Available values: `0` `1`
	 * @urlParam   deleted integer integer static(1) Available values: `0` `1`
	 * @urlParam   orderBy string string static(id:asc|data_envio:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `text` `descricao`
	 * @urlParam   data_envio[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   data_envio[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
     * 
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws ValidationException
	 */
	public function index(Request $request)
	{
		$this->validate($request, [
			'limit'         => 'sometimes|nullable|integer',
			'page'          => 'sometimes|nullable|integer',
			'orderBy'       => 'sometimes|nullable|string',
			'search'        => 'sometimes|nullable|string',
			'read'          => 'sometimes|integer',
			'deleted'       => 'sometimes|integer',
			'data_envio'    => 'sometimes|array',
			'data_envio.*'  => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

        // Get current user
        $user = $this->user();

        $items = Notifications::query()
            ->isActive()
            ->with(['customers' => function($query) use ($user) {
                $query->where('customers.id', $user->id);
            }])
            // Força o carregamento mesmo sem relacionamento
            ->doesntHave('customers', 'or', function($query) use ($user) {
                $query->where('customers.id', $user->id);
            });

		// only Active
		$items->isActive();

		$search             = $request->input('search');
		$orderBy            = $this->getOrderBy($request->input('orderBy'));
		$limit              = $this->getLimit($request->input('limit'));
		$read               = $request->input('read');
        $deleted            = $request->input('deleted');
		$data_envio_start   = $request->has('data_envio.0') ? Carbon::parse($request->input('data_envio.0'))->setTimezone('UTC') : null;
		$data_envio_end     = $request->has('data_envio.1') ? Carbon::parse($request->input('data_envio.1'))->setTimezone('UTC') : null;

        // read
        if ($read !== null) {
            $items->whereHas('customers', function($query) use ($user, $read) {
                $query->where('customers.id', $user->id)
                    ->where('customer_has_notifications.read', $read);
            });
        }

        // deleted
        if ($deleted !== null && $deleted != 0) {
            $items->whereHas('customers', function($query) use ($user, $deleted) {
                $query->where('customers.id', $user->id)
                    ->where('customer_has_notifications.deleted', $deleted);
            });
        }
        else
        {
            // Por padrão, mostra apenas não deletadas OU sem relacionamento
            $items->where(function($query) use ($user) {
                $query->whereHas('customers', function($q) use ($user) {
                    $q->where('customers.id', $user->id)
                    ->where('customer_has_notifications.deleted', false);
                })->orWhereDoesntHave('customers', function($q) use ($user) {
                    $q->where('customers.id', $user->id);
                });
            });
        }

		// data_envio
		if( $data_envio_start )
		{
			$items->where('data_envio', '>=', $data_envio_start);
		}
		if( $data_envio_end )
		{
			$items->where('data_envio', '<=', $data_envio_end);
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

		$items = $items->paginate($limit)->appends($request->except('page'));

		return NotificationsResource::collection($items);
	}

	/**
	 * Get notification
	 *
	 * @param Notifications $notification
	 *
	 * @return NotificationsResource
	 */
	public function show(Notifications $notification)
	{
		return new NotificationsResource($notification);
	}

	/**
	 * Remove notification
	 *
	 * @param Notifications $notification
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function destroy(Notifications $notification)
    {
        DB::beginTransaction();
    
        try {
            // Get current user
            $user = $this->user();
            
             // Verifica se já existe o relacionamento e obtém o valor atual de 'deleted'
            $existingRelation = $user->notifications()
                                  ->where('notifications.id', $notification->id)
                                  ->first();
            
            // Determina o novo valor (alterna o atual)
            $newDeleteValue = $existingRelation ? !$existingRelation->pivot->deleted : true;

            // Verifica se já existe o relacionamento
            if ($existingRelation) {
                // Atualiza o registro existente
                $user->notifications()->updateExistingPivot($notification->id, [
                    'deleted' => $newDeleteValue,
                    'updated_at' => now()
                ]);
            } else {
                // Cria um novo registro marcado como deletado
                $user->notifications()->attach($notification->id, [
                    'deleted' => $newDeleteValue,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
        }
        catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    
        // Save log
        $logMessage = $newDeleteValue 
        ? 'Marcou notificação como deletado' 
        : 'Desmarcou a notificação como deletado';

        // Save log
        $this->log(__FUNCTION__, $notification->name, $logMessage, $notification);
    
        return response(null, 204);
    }

	/**
	 * Remove all notification
	 *
	 * @param Notifications $notification
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function destroyAll()
    {
        DB::beginTransaction();

        try {
            // Get current user
            $user = $this->user();
            $now = now();
            
            // Obter IDs de todas as notificações
            $allNotificationIds = Notifications::where('is_active',true)->pluck('id')->toArray();

            // Obter IDs das notificações que o usuário já tem relacionamento
            $existingNotificationIds = $user->notifications()
                                        ->pluck('notifications.id')
                                        ->toArray();
            
            // Notificações que precisam ser atualizadas
            $toUpdate = array_intersect($allNotificationIds, $existingNotificationIds);
            
            // Notificações que precisam ser criadas
            $toCreate = array_diff($allNotificationIds, $existingNotificationIds);
            
            // Atualiza as existentes
            if (!empty($toUpdate)) {
                $user->notifications()->whereIn('notifications.id', $toUpdate)
                    ->update([
                        'deleted' => true,
                        'updated_at' => $now
                    ]);
            }
            
            // Cria as novas relações
            if (!empty($toCreate)) {
                $insertData = array_map(function($id) use ($user, $now) {
                    return [
                        'notification_id' => $id,
                        'user_id' => $user->id,
                        'deleted' => true,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }, $toCreate);

                $user->notifications()->insert($insertData);
            }

            DB::commit();
        }
        catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Save log
        $this->log(__FUNCTION__, $user->name, 'Marcou todas as notificações como deletadas', $user);

        return response(null, 204);
    }

	/**
	 * read notification
	 *
	 * @param Notifications $notification
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function read(Notifications $notification)
    {
        DB::beginTransaction();
    
        try {
            // Get current user
            $user = $this->user();

             // Verifica se já existe o relacionamento e obtém o valor atual de 'read'
            $existingRelation = $user->notifications()
                                  ->where('notifications.id', $notification->id)
                                  ->first();
            
            // Determina o novo valor (alterna o atual)
            $newReadValue = $existingRelation ? !$existingRelation->pivot->read : true;
           
            // Verifica se já existe o relacionamento
            if ($existingRelation) {
                // Atualiza o registro existente
                $user->notifications()->updateExistingPivot($notification->id, [
                    'read' => $newReadValue,
                    'updated_at' => now()
                ]);
            } else {
                // Cria um novo registro marcado como deletado
                $user->notifications()->attach($notification->id, [
                    'read' => $newReadValue,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();
        }
        catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    
        // Save log
        $logMessage = $newReadValue 
            ? 'Marcou notificação como lido' 
            : 'Marcou notificação como não lido';

        // Save log
        $this->log(__FUNCTION__, $notification->name, $logMessage, $notification);
    
        return response(null, 204);
    }

	/**
	 * read all notification
	 *
	 * @param Notifications $notification
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function readAll()
    {
        DB::beginTransaction();

        try {
            // Get current user
            $user = $this->user();
            $now = now();
            
            // Obter IDs de todas as notificações
            $allNotificationIds = Notifications::where('is_active',true)->pluck('id')->toArray();

            // Obter IDs das notificações que o usuário já tem relacionamento
            $existingNotificationIds = $user->notifications()
                                        ->pluck('notifications.id')
                                        ->toArray();
            
            // Notificações que precisam ser atualizadas
            $toUpdate = array_intersect($allNotificationIds, $existingNotificationIds);
            
            // Notificações que precisam ser criadas
            $toCreate = array_diff($allNotificationIds, $existingNotificationIds);
            
            // Atualiza as existentes
            if (!empty($toUpdate)) {
                $user->notifications()->whereIn('notifications.id', $toUpdate)
                    ->update([
                        'read' => true,
                        'updated_at' => $now
                    ]);
            }
            
            // Cria as novas relações
            if (!empty($toCreate)) {
                $insertData = array_map(function($id) use ($user, $now) {
                    return [
                        'notification_id' => $id,
                        'user_id' => $user->id,
                        'read' => true,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }, $toCreate);

                $user->notifications()->insert($insertData);
            }

            DB::commit();
        }
        catch(Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Save log
        $this->log(__FUNCTION__, $user->name, 'Marcou todas as notificações como lidas', $user);

        return response(null, 204);
    }
}
