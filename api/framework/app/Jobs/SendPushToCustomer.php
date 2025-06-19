<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SendPushToCustomer implements ShouldQueue
{

	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Customer
	 *
	 * @var Customer
	 */
	public $customer;

	/**
	 * Title
	 *
	 * @var integer
	 */
	public $title;

	/**
	 * Message
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Data
	 *
	 * @var string
	 */
	public $data;

	/**
	 * Collapse id
	 *
	 * @var string
	 */
	public $collapse_id;

	/**
	 * Create a new job instance.
	 *
	 * @param Customer    $customer
	 * @param             $title
	 * @param             $message
	 * @param array       $data
	 * @param string|null $collapse_id
	 *
	 * @return void
	 */
	public function __construct(Customer $customer, $title, $message, $data = [], $collapse_id = null)
	{
		$this->customer    = $customer->withoutRelations();
		$this->title       = Str::limit($title, 50);
		$this->message     = Str::limit($message, 150);
		$this->data        = $data;
		$this->collapse_id = $collapse_id;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$data = [
			'app_id'         => config('api.onesignal_app_id'),
			'data'           => $this->data,
			'contents'       => [
				'en' => $this->message,
			],
			'ios_badgeType'  => 'Increase',
			'ios_badgeCount' => 1,
			'headings'       => [
				'en' => $this->title,
			],
		];

		if( $this->collapse_id )
		{
			$data['collapse_id'] = $this->collapse_id;
		}

		// Local
		if( is_environment_local() )
		{
			// Test users
			$data['included_segments'] = config('api.onesignal_segment_test');
		}
		else
		{
			// Filters
			$data['filters'] = [
				[
					'field'    => 'tag',
					'key'      => 'user_id',
					'relation' => '=',
					'value'    => $this->customer->uuid,
				],
				[
					'field'    => 'tag',
					'key'      => 'silent',
					'relation' => 'not_exists',
				],
			];
		}

		$response = Http::withHeaders([
			'Authorization' => 'Basic ' . config('api.onesignal_api_key'),
			'Content-Type'  => 'application/json; charset=utf-8',
		])->post('https://onesignal.com/api/v1/notifications', $data);
	}
}
