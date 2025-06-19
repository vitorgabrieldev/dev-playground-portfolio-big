<?php

namespace App\Logging;

use Illuminate\Support\Str;
use Monolog\Formatter\NormalizerFormatter;

class CustomLoggerFormatter extends NormalizerFormatter
{

	/**
	 * @param array $record
	 *
	 * @return array|mixed|string
	 */
	public function format(array $record)
	{
		$record = parent::format($record);

		return $this->getDocument($record);
	}

	/**
	 * Convert a log message into an Log entity
	 *
	 * @param array $record
	 *
	 * @return array
	 */
	protected function getDocument(array $record)
	{
		$request = request();

		$qs = $request->getQueryString();

		// Get current user
		$user = $request->user();

		if( !$user )
		{
			// Search user by guards
			foreach( array_keys(config('auth.guards')) as $guard )
			{
				$user = $request->user($guard);

				if( $user ) break;
			}
		}

		return [
			'uuid'       => uuid(),
			'user_type'  => $user ? $user->getMorphClass() : null,
			'user_id'    => $user->id ?? null,
			'level'      => $record['level_name'],
			'message'    => $record['message'],
			'context'    => $record['context'],
			'ip'         => $request->ip(),
			'user_agent' => Str::ascii($request->userAgent()),
			'url'        => urldecode($request->getPathInfo() . ($qs !== null ? '?' . $qs : '')),
			'method'     => $request->getMethod(),
		];
	}
}
