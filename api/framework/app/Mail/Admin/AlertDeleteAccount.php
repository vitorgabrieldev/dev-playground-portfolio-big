<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertDeleteAccount extends Mailable
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
		return $this->subject('Conta deletada em ' . config('app.name_short'))->markdown('emails.admin.alert_delete_account')->with([
			'user'     => $this->user,
		]);
	}
}
