<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable
{

	use Queueable, SerializesModels;

	/**
	 * User
	 * 
	 * @var Customer
	 */
	public $user;

	/**
	 * token
	 *
	 * @var string
	 */
	public $token;

	/**
	 * Create a new message instance.
	 *
	 * @param $user
	 * @param $token
	 */
	public function __construct($user, $token)
	{
		$this->user     = $user;
		$this->token = $token;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->subject('E-mail de verificação ' . config('app.name_short') . ' - ' . $this->user->name)->markdown('emails.customer.verify-email')->with([
			'user'     => $this->user,
			'token' => $this->token,
		]);
	}
}
