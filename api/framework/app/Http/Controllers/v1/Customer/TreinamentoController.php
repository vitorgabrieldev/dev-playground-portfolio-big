<?php

namespace App\Http\Controllers\v1\Customer;

use App\Http\Resources\Customer\TreinamentoResource;
use App\Models\CategoriasTreinamento;
use App\Models\Treinamento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


/**
 * Class TreinamentoController
 *
 * @resource Treinamento
 * @package  App\Http\Controllers\v1\Customer
 */
class TreinamentoController extends Controller
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
	];

	/**
	 * Fields available for search
	 *
	 * @var array
	 */
	protected $fieldSearchable = [
		'uuid',
		'name',
        'descricao',
	];

	/**
	 * Get all treinamentos
	 *
	 * @param Request $request
	 *
	 * @urlParam   limit integer integer static(20) Default: `20` Max: `100`
	 * @urlParam   page integer integer static(1)
	 * @urlParam   categoria_treinamento_id string string|max:191 uuid
	 * @urlParam   read integer integer static(1) Available values: `0` `1`
	 * @urlParam   favorited integer integer static(1) Available values: `0` `1`
	 * @urlParam   orderBy string string static(id:asc|created_at:asc) Default: `id:asc`.<br>Available fields: `id` `name` `order` `created_at` `updated_at`
	 * @urlParam   search string string static(lorem) Search in the fields: `uuid` `text` `descricao`
	 * @urlParam   created_at[0] string string static(2019-07-29T00:00:00-03:00) ISO 8601 `Y-m-d\TH:i:sP` Initial date
	 * @urlParam   created_at[1] string string static(2019-07-29T23:59:59-03:00) ISO 8601 `Y-m-d\TH:i:sP` Final date
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
            'categoria_treinamento_id'	=> 'sometimes|nullable|string|exists:' . (new CategoriasTreinamento())->getTable() . ',uuid',
			'read'          => 'sometimes|integer',
			'favorited'       => 'sometimes|integer',
			'created_at'    => 'sometimes|array',
			'created_at.*'  => 'sometimes|date_format:Y-m-d\TH:i:sP',
		]);

        // Get current user
        $user = $this->user();

        $items = Treinamento::query()
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
		$categoria_treinamento_id  = $request->input('categoria_treinamento_id');
		$read               = $request->input('read');
        $favorited            = $request->input('favorited');
		$created_at_start   = $request->has('created_at.0') ? Carbon::parse($request->input('created_at.0'))->setTimezone('UTC') : null;
		$created_at_end     = $request->has('created_at.1') ? Carbon::parse($request->input('created_at.1'))->setTimezone('UTC') : null;

        // read
        if ($read !== null) {
            $items->whereHas('customers', function($query) use ($user, $read) {
                $query->where('customers.id', $user->id)
                    ->where('customer_has_treinamento.read', $read);
            });
        }

        // favorited
        if ($favorited !== null) {
            $items->whereHas('customers', function($query) use ($user, $favorited) {
                $query->where('customers.id', $user->id)
                    ->where('customer_has_treinamento.favorited', $favorited);
            });
        }

		if($categoria_treinamento_id)
		{
			$categoria = CategoriasTreinamento::where("uuid", $categoria_treinamento_id)->first(["id","name"]) ?? null;
			if(!$categoria)
			{
				throw ValidationException::withMessages(['categoria_treinamento_id' => __('Categoria não encontrada. Altere ou tente novamente. Caso persista, entre em contato com o suporte.')]);
			}

			$items->whereRelation('categoria', 'id', $categoria->id);
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

		$items = $items->paginate($limit)->appends($request->except('page'));

		return TreinamentoResource::collection($items);
	}

	/**
	 * Get treinamento
	 *
	 * @param Treinamento $treinamento
	 *
	 * @return TreinamentoResource
	 */
	public function show(Treinamento $treinamento)
	{
        $treinamento->load(['categoria']);

		return new TreinamentoResource($treinamento);
	}

	/**
	 * favorite treinamento
	 *
	 * @param Treinamento $treinamento
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function favorite(Treinamento $treinamento)
    {
        DB::beginTransaction();
    
        try {
            // Get current user
            $user = $this->user();
            
            // Verifica se já existe o relacionamento e obtém o valor atual de 'favorited'
            $existingRelation = $user->treinamentos()
                                  ->where('treinamento.id', $treinamento->id)
                                  ->first();
            
            // Determina o novo valor (alterna o atual)
            $newFavoritedValue = $existingRelation ? !$existingRelation->pivot->favorited : true;

            // Verifica se já existe o relacionamento
            if ($existingRelation) {
                // Atualiza o registro existente
                $user->treinamentos()->updateExistingPivot($treinamento->id, [
                    'favorited' => $newFavoritedValue,
                    'updated_at' => now()
                ]);
            } else {
                // Cria um novo registro marcado como favorito
                $user->treinamentos()->attach($treinamento->id, [
                    'favorited' => $newFavoritedValue,
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
        $logMessage = $newFavoritedValue 
        ? 'Marcou treinamento como favorito' 
        : 'Desmarcou o treinamento como favorito';
    
        // Save log
        $this->log(__FUNCTION__, $treinamento->name, $logMessage, $treinamento);
    
        return response(null, 204);
    }

	/**
	 * read treinamento
	 *
	 * @param Treinamento $treinamento
	 *
	 * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
	 * @throws Throwable
	 */
    public function read(Treinamento $treinamento)
    {
        DB::beginTransaction();
    
        try {
            // Get current user
            $user = $this->user();
            
            // Verifica se já existe o relacionamento e obtém o valor atual de 'read'
            $existingRelation = $user->treinamentos()
                                  ->where('treinamento.id', $treinamento->id)
                                  ->first();
            
            // Determina o novo valor (alterna o atual)
            $newReadValue = $existingRelation ? !$existingRelation->pivot->read : true;
            
            if ($existingRelation) {
                // Atualiza o registro existente com o valor invertido
                $user->treinamentos()->updateExistingPivot($treinamento->id, [
                    'read' => $newReadValue,
                    'updated_at' => now()
                ]);
            } else {
                // Cria um novo registro marcado como lido (true por padrão)
                $user->treinamentos()->attach($treinamento->id, [
                    'read' => $newReadValue, // será true para novos registros
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
            ? 'Marcou treinamento como lido' 
            : 'Marcou treinamento como não lido';
        
        $this->log(__FUNCTION__, $treinamento->name, $logMessage, $treinamento);
    
        return response(null, 204);
    }

}
