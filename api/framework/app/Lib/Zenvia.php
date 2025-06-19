<?php

namespace App\Lib;

use App\Exceptions\GeneralException;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Zenvia
{

	/**
	 * API base
	 *
	 * @var string
	 */
	private string $api;

	/**
	 * @var string
	 */
	private string $apiToken;

	/**
	 * @var string
	 */
	private string $from;

	function __construct()
	{
		$this->api      = 'https://api.zenvia.com/v2';
		$this->apiToken = config('api.zenvia_api_token');
		$this->from     = config('api.zenvia_from');

		return $this;
	}

	/**
	 * Send SMS
	 *
	 * @param string $number
	 * @param string $message
	 *
	 * @return object
	 * @throws Exception
	 */
	function sendSMS(string $number, string $message)
	{
		$params = [
			'from'     => $this->from,
			'to'       => only_numbers($number),
			'contents' => [
				[
					'type' => 'text',
					'text' => $message,
				],
			],
		];

		$response = $this->send('post', '/channels/sms/messages', $params);

		return (object) [
			'id' => $response->id,
		];
	}

	/**
	 * Send request
	 *
	 * @param       $method
	 * @param       $url
	 * @param array $data
	 *
	 * @return object
	 * @throws Exception
	 */
	private function send($method, $url, array $data = [])
	{
		$response = Http::baseUrl($this->api)->contentType('application/json; charset=utf-8')->acceptJson()->withHeaders([
			'X-API-TOKEN' => $this->apiToken,
		]);

		if( is_environment_local() )
		{
			clock('----');
			clock($method);
			clock($url);
			clock($data);
			clock($response);
		}

		if( $method === 'get' )
		{
			$response = $response->get($url, $data);
		}
		elseif( $method === 'post' )
		{
			$response = $response->post($url, $data);
		}

		if( is_environment_local() )
		{
			clock($response->object());
		}

		$responseData = $response->object();

		if( isset($responseData->exception) )
		{
			$error = $responseData->exception->message;

			throw GeneralException::free($error);
		}

		if( $response->status() >= 400 and $response->status() <= 500 )
		{
			$responseData = $response->object();

			$error = $responseData->message;

			if( $responseData->code === 'VALIDATION_ERROR' )
			{
				$error = 'Erro de validação no envio do SMS.';

				// Log error
				Log::debug('Zenvia validation error:' . $responseData->message);
			}

			throw GeneralException::free($error);
		}

		return $response->object();
	}
}