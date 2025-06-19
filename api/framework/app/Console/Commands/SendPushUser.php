<?php

namespace App\Console\Commands;

use App\Models\PushUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPushUser extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'push-user:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send push to user';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$items = PushUser::query();

		// Load relations
		$items->with('user');

		// Just not sent
		$items->whereNull('send_at');

		$items->where(function ($query) {
			// Send now
			$query->whereNull('scheduled_at');

			// Scheduled
			$query->orWhere('scheduled_at', '<=', now());
		});

		$items = $items->orderBy('created_at', 'asc')->get();

		foreach( $items as $item )
		{
			$data = [
				'app_id'         => config('api.onesignal_app_id'),
				'data'           => [
					'id'   => $item->id,
					'type' => 'user',
				],
				'contents'       => [
					'en' => $item->body,
				],
				'ios_badgeType'  => 'Increase',
				'ios_badgeCount' => 1,
				'headings'       => [
					'en' => $item->title,
				],
			];

			// External url
			if( $item->url )
			{
				$data['url'] = $item->url;
			}

			// Local
			// if( is_environment_local() )
			// {
			// 	// Test users
			// 	$data['included_segments'] = config('api.onesignal_segment_test');
			// }
			// else
			// {
				// Filters
				$data['filters'] = [
					[
						'field'    => 'tag',
						'key'      => 'user_id',
						'relation' => '=',
						'value'    => $item->user->uuid,
					],
					[
						'field'    => 'tag',
						'key'      => 'silent',
						'relation' => 'not_exists',
					],
				];
			// }

			if((bool)$item->user->notify_general)
			{
				$response = Http::withHeaders([
					'Authorization' => 'Basic ' . config('api.onesignal_api_key'),
					'Content-Type'  => 'application/json; charset=utf-8',
				])->post('https://api.onesignal.com/notifications?c=push', $data);

				$item->api_response = $response;
			}

			// Update
			$item->send_at      = now();
			$item->timestamps   = false;
			$item->save();
		}
	}
}
