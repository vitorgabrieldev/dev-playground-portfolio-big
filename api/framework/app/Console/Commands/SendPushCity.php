<?php

namespace App\Console\Commands;

use App\Models\PushCity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendPushCity extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'push-city:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send push to city users';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function handle()
	{
		$items = PushCity::query();

		// Load relations
		$items->with('city');

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
					'type' => 'city',
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
						'key'      => 'city_id',
						'relation' => '=',
						'value'    => $item->city->uuid,
					],
					[
						'field'    => 'tag',
						'key'      => 'silent',
						'relation' => 'not_exists',
					],
				];
			// }

			$response = Http::withHeaders([
				'Authorization' => 'Basic ' . config('api.onesignal_api_key'),
				'Content-Type'  => 'application/json; charset=utf-8',
			])->post('https://api.onesignal.com/notifications?c=push', $data);

			// Update
			$item->send_at      = now();
			$item->total_users  = $response['recipients'] ?? 0;
			$item->api_response = $response;
			$item->timestamps   = false;
			$item->save();
		}
	}
}
