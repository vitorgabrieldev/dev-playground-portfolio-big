<?php

namespace App\Logging;

use Monolog\Logger;

class CustomLogger
{

	/**
	 * Create a custom Monolog instance.
	 *
	 * @param array $config
	 *
	 * @return \Monolog\Logger
	 */
	public function __invoke(array $config)
	{
		$logger = new Logger('custom');
		$logger->pushHandler(new CustomLoggerHandler());

		return $logger;
	}
}
