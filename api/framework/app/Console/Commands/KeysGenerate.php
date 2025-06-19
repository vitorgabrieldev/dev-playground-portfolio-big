<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class KeysGenerate extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'keys:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate keys to APP_KEY, PUSHER_APP_ID, PUSHER_APP_KEY and PUSHER_APP_SECRET';

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
		$data = [
			'APP_KEY'           => 'base64:' . base64_encode(Encrypter::generateKey($this->laravel['config']['app.cipher'])),
			'PUSHER_APP_ID'     => Str::random(12),
			'PUSHER_APP_KEY'    => Str::random(32),
			'PUSHER_APP_SECRET' => Str::random(64),
		];

		$this->info("Keys:\n" . implode("\n", array_map(function ($v, $k) {
				return sprintf("%s=%s", $k, $v);
			}, $data, array_keys($data))));

		$this->processFile($data, base_path('.env'));
		$this->processFile($data, base_path('.env-local'));
		$this->processFile($data, base_path('.env-production'));
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function processFile($data, $file)
	{
		if( !$this->files->exists($file) )
		{
			$this->warn('Skip file ' . $file . '');

			return;
		}

		$this->info('Writing in ' . basename($file));

		$contents = $this->files->get($file);
		$contents = preg_replace("/^APP_KEY=(.*)\n/m", 'APP_KEY=' . $data['APP_KEY'] . "\n", $contents);
		$contents = preg_replace("/^PUSHER_APP_ID=(.*)\n/m", 'PUSHER_APP_ID=' . $data['PUSHER_APP_ID'] . "\n", $contents);
		$contents = preg_replace("/^PUSHER_APP_KEY=(.*)\n/m", 'PUSHER_APP_KEY=' . $data['PUSHER_APP_KEY'] . "\n", $contents);
		$contents = preg_replace("/^PUSHER_APP_SECRET=(.*)\n/m", 'PUSHER_APP_SECRET=' . $data['PUSHER_APP_SECRET'] . "\n", $contents);

		$this->files->put($file, $contents);
	}
}
