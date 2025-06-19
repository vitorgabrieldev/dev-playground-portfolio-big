<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovedMail extends Mailable
{

	use Queueable, SerializesModels;

	/**
	 * User
	 * 
	 * @var Customer
	 */
	public $user;

	/**
	 * Create a new message instance.
	 *
	 * @param $user
	 * @param $token
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
		return $this->subject('Bem vindo ao Grupo AIZ - ' . $this->user->name)->markdown('emails.customer.approved-email')->with([
			'user'     => $this->user,
		]);
	}
}
