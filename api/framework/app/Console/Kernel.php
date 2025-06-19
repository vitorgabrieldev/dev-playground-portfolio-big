<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [//
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param \Illuminate\Console\Scheduling\Schedule $schedule
	 *
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		// $schedule->command('inspire')->hourly();

		$schedule->command('push-general:send')->everyMinute()->withoutOverlapping(720);
		$schedule->command('push-city:send')->everyMinute()->withoutOverlapping(720);
		$schedule->command('push-state:send')->everyMinute()->withoutOverlapping(720);
		$schedule->command('push-user:send')->everyMinute()->withoutOverlapping(720);

		// Socket
		$schedule->command('websockets:clean')->daily();
	}

	/**
	 * Register the commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		$this->load(__DIR__ . '/Commands');

		require base_path('routes/console.php');
	}
}
