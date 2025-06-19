<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Filesystem\Filesystem;
use LogicException;
use Throwable;

class ConfigCache extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'config:cache';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a cache file for faster configuration loading';

	/**
	 * The filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Create a new config cache command instance.
	 *
	 * @param \Illuminate\Filesystem\Filesystem $files
	 *
	 * @return void
	 */
	public function __construct(Filesystem $files)
	{
		parent::__construct();

		$this->files = $files;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function handle()
	{
		$this->call('config:clear');

		$config = $this->getFreshConfiguration();

		$configPath = $this->laravel->getCachedConfigPath();

		$this->files->put($configPath, '<?php return ' . var_export($config, true) . ';' . PHP_EOL);

		try
		{
			require $configPath;
		}
		catch( Throwable $e )
		{
			$this->files->delete($configPath);

			throw new LogicException('Your configuration files are not serializable.', 0, $e);
		}

		$this->info('Configuration cached successfully!');
	}

	/**
	 * Boot a fresh copy of the application configuration.
	 *
	 * @return array
	 */
	protected function getFreshConfiguration()
	{
		$app = require $this->laravel->bootstrapPath() . '/app.php';

		$app->useStoragePath($this->laravel->storagePath());

		$app->make(ConsoleKernelContract::class)->bootstrap();

		$configDatabase = Setting::pluck('val', 'key')->toArray();

		// Merge configuration
		$app['config']->set($configDatabase);

		$this->info('Merged configuration');

		return $app['config']->all();
	}
}
