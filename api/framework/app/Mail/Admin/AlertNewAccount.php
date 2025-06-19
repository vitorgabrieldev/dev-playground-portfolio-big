<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertNewAccount extends Mailable
{

	use Queueable, SerializesModels;

	/**
	 * User
	 */
	public $user;

	/**
	 * Create a new message instance.
	 *
	 * @param $user
	 */
	public function __construct($user)
	{
		$this->user     = $user;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->subject('Nova conta registrada em ' . config('app.name_short'))->markdown('emails.admin.alert_new_account')->with([
			'user'     => $this->user,
		]);
	}
}
