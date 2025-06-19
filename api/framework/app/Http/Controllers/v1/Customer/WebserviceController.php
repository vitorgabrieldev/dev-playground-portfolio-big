<?php

namespace App\Http\Controllers\v1\Customer;

use App\Exceptions\GeneralException;
use App\Http\Resources\Customer\StateResource;
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
 * @package  App\Http\Controllers\v1\Customer
 */
class WebserviceController extends Controller
{
	/**
	 * States
	 *
	 * @param Request $request
	 *
	 * @unauthenticated
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
			$items->where('name', 'like', '%' . $search . '%');
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
	 * @unauthenticated
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
			// DB::Raw('CONCAT_WS("-", ' . $tableCity . '.name, ' . $tableState . '.abbr) as full_name'),
			DB::raw($this->concatFullName($tableCity, $tableState) . ' as full_name'),
			$tableState . '.uuid as state_id',
			$tableState . '.abbr as state_abbr',
			$tableState . '.name as state_name',
		]);

		// State join
		$items->join($tableState, $tableCity . '.state_id', '=', $tableState . '.id');

		if( $search )
		{
			// Start Search 'a%'
			// $items->where(DB::Raw('CONCAT_WS("-", ' . $tableCity . '.name, ' . $tableState . '.abbr)'), 'like', $search . '%');
			$items->where(DB::raw($this->concatFullName($tableCity, $tableState)), 'like', $search . '%');
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
	 * @unauthenticated
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
				'city_id'        => $city->uuid,
				'city_name'      => $city->name,
				'city_full_name' => $city->name . '-' . $city->state->abbr,
				'state_id'       => $city->state->uuid,
				'state_abbr'     => $city->state->abbr,
				'state_name'     => $city->state->name,
			]);
		}
		catch( Exception $e )
		{
			throw GeneralException::make('zipcode_not_found');
		}
	}

	/**
	 * Get localization by lat lon
	 *
	 * @param Request $request
	 * 
	 * @urlParam   lat numeric string static(-12.065464)
	 * @urlParam   lon numeric string static(-12.065464)
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 * @throws \Illuminate\Validation\ValidationException
	 * @throws Exception
	 */
	public function localizationLatLon(Request $request)
	{
		try
		{
			$this->validate($request, [
				'lat' => 'required',
				'lon' => 'required',
			]);

			$lat = $request->input('lat');
			$lon = $request->input('lon');

			$params = [];

			$params[] = 'key=' . config('api.googlemaps_key');
			$params[] = 'language=pt-BR';
			$params[] = 'latlng=' . $lat . ',' . $lon;
			$params[] = 'location_type=ROOFTOP&result_type=street_address';

			try
			{
				$client   = new Client();
				$response = $client->request('GET', 'https://maps.googleapis.com/maps/api/geocode/json?' . implode('&',$params));

				$attributes = json_decode($response->getBody(), true);

				if( $attributes['status'] !== 'OK' && $attributes['status'] !== 'ZERO_RESULTS' )
				{
					throw new Exception($attributes['error_message'] ?? 'Ocorreu um erro, por favor tente novamente.');
				}
			}
			catch( Exception $e )
			{
				throw $e;
			}

			if( !isset($attributes['results']) OR !count($attributes['results']) )
			{
				abort(404);
			}

			foreach( $attributes['results'][0]['address_components'] as $address )
			{
				if( $address['types'][0] == 'street_number' )
				{
					$number = $address['long_name'];
				}
				else if( $address['types'][0] == 'route' )
				{
					$street = $address['long_name'];
				}
				else if( array_search('sublocality', $address['types']) > -1 )
				{
					$district = $address['long_name'];
				}
				else if( array_search('administrative_area_level_2', $address['types']) > -1 )
				{
					$city = $address['long_name'];
				}
				else if( array_search('administrative_area_level_1', $address['types']) > -1 )
				{
					$state = $address['short_name'];
				}
				else if( $address['types'][0] == 'postal_code' )
				{
					$zipcode = $address['long_name'];
				}
			}	

			$result = $attributes['results'][0];
			$data = [
				'id'       => $result['place_id'],
				'city_id'  => isset($city) ? (((new City())->where("name", $city)->first())->id ?? $result['place_id']) : $result['place_id'],
				'city_name'  => isset($city) ? (((new City())->where("name", $city)->first())->name ?? $result['place_id']) : $result['place_id'],
				'state_id'  => isset($state) ? (((new State())->where("abbr", $state)->first())->id ?? $state) : 0,
				'state_name'  => isset($state) ? (((new State())->where("abbr", $state)->first())->abbr ?? $state) : "",
				'name'     => $result['formatted_address'],
				'lat'      => (float) $result['geometry']['location']['lat'],
				'lon'      => (float) $result['geometry']['location']['lng'],
				'street'   => isset($street) ? $street : "",
				'number'   => isset($number) ? $number : "",
				'district' => isset($district) ? $district : "",
				'zipcode'  => isset($zipcode) ? $zipcode : "",
				'city'     => isset($city) ? $city : "",
				'state'    => isset($state) ? $state : ""
			];

			return response()->json(['data' => $data]);
		}
		catch (\Throwable $th)
		{
			dd($th->getMessage());
			//throw $th;
		}
	}

	private function concatFullName($tableCity, $tableState)
	{
		if (DB::getDriverName() === 'sqlite') {
			return $tableCity . '.name || "-" || ' . $tableState . '.abbr';
		}
	
		return 'CONCAT_WS("-", ' . $tableCity . '.name, ' . $tableState . '.abbr)';
	}
	
}