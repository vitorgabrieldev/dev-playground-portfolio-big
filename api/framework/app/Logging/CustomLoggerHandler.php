<?php

namespace App\Logging;

use App\Models\SystemLog;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;

class CustomLoggerHandler extends AbstractProcessingHandler
{

	/**
	 * @param array $record
	 */
	protected function write(array $record): void
	{
		try
		{
			$log = new SystemLog();
			$log->fill($record['formatted']);
			$log->save();
		}
		catch( Exception $e )
		{
			//
		}
	}

	/**
	 * @return FormatterInterface
	 */
	protected function getDefaultFormatter(): FormatterInterface
	{
		return new CustomLoggerFormatter();
	}
}
