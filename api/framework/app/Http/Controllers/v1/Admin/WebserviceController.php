<?php

namespace App\Http\Controllers\v1\Admin;

use App\Exceptions\GeneralException;
use App\Http\Resources\Admin\StateResource;
use App\Models\City;
use App\Models\State;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class WebserviceController
 *
 * @resource Webservice
 * @package  App\Http\Controllers\v1\Admin
 */
class WebserviceController extends Controller
{

	/**
	 * States
	 *
	 * @param Request $request
	 *
	 * @urlParam   search string string static(lorem) Search in the fields: `name`
	 *
	 * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function states(Request $request)
	{
		$this->validate($request, [
			'search' => 'nullable|string',
		]);

		$items = State::query();

		$search = $request->input('search');

		if( $search )
		{
			$items->where('name', 'like', '%' . $search . '%')->orWhere('abbr', 'like', '%' . $search . '%');
		}

		$items->orderBy('name', 'asc');

		$items = $items->get();

		return StateResource::collection($items);
	}

	/**
	 * Cities
	 *
	 * Search city, must enter at least one of the search fields `search` or `state_abbr`<br>List a maximum of `50` items.
	 *
	 * @param Request $request
	 *
	 * @urlParam   search string string static(lorem) Search in the fields: `full_name`
	 * @urlParam   state_abbr string string static(PR)
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function cities(Request $request)
	{
		$this->validate($request, [
			'search'     => 'nullable|string',
			'state_abbr' => 'nullable|string',
		]);

		$search     = $request->input('search');
		$state_abbr = $request->input('state_abbr');

		if( !$search and !$state_abbr )
		{
			throw GeneralException::make('required_search_field');
		}

		$tableCity  = (new City)->getTable();
		$tableState = (new State)->getTable();

		$items = City::query();

		// Select
		$items->select([
			$tableCity . '.*',
			DB::Raw('CONCAT_WS("-", ' . $tableCity . '.name, ' . $tableState . '.abbr) as full_name'),
			$tableState . '.uuid as state_id',
			$tableState . '.abbr as state_abbr',
			$tableState . '.name as state_name',
		]);

		// State join
		$items->join($tableState, $tableCity . '.state_id', '=', $tableState . '.id');

		if( $search )
		{
			// Start Search 'a%'
			$items->where(DB::Raw('CONCAT_WS("-", ' . $tableCity . '.name, ' . $tableState . '.abbr)'), 'like', $search . '%');
		}

		if( $state_abbr )
		{
			$items->where($tableState . '.abbr', '=', $state_abbr);
		}

		$items->orderBy($tableCity . '.name', 'asc');

		$items = $items->limit(50)->get();

		// Remove id from response
		$items->transform(function ($item) {
			unset($item->id);

			return $item;
		});

		return response()->json(['data' => $items]);
	}

	/**
	 * Find zipcode
	 *
	 * @param $zipcode
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function zipcode($zipcode)
	{
		try
		{
			$client   = new Client();
			$response = $client->request('GET', 'https://viacep.com.br/ws/' . $zipcode . '/json');

			$attributes = json_decode($response->getBody(), true);

			if( array_key_exists('erro', $attributes) and $attributes['erro'] === true )
			{
				throw GeneralException::make('zipcode_not_found');
			}

			$city = City::where('name', '=', $attributes['localidade'])->with(['state'])->whereRelation('state', 'abbr', '=', $attributes['uf'])->first();

			if( !$city )
			{
				throw GeneralException::make('zipcode_not_found');
			}

			return response()->json([
				'zipcode'        => $attributes['cep'],
				'street'         => $attributes['logradouro'],
				'district'       => $attributes['bairro'],
				'city_id'        => $city->id,
				'city_name'      => $city->name,
				'city_full_name' => $city->name . '-' . $city->state->abbr,
				'state_id'       => $city->state->id,
				'state_abbr'     => $city->state->abbr,
				'state_name'     => $city->state->name,
			]);
		}
		catch( Exception $e )
		{
			throw GeneralException::make('zipcode_not_found');
		}
	}
}
