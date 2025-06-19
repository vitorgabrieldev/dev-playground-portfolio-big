<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordMail extends Mailable
{

	use Queueable, SerializesModels;

	/**
	 * User
	 */
	public $user;

	/**
	 * Password
	 *
	 * @var string
	 */
	public $password;

	/**
	 * Create a new message instance.
	 *
	 * @param $user
	 * @param $password
	 */
	public function __construct($user, $password)
	{
		$this->user     = $user;
		$this->password = $password;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->subject('Acesso ao administrador ' . config('app.name_short') . ' - ' . $this->user->name)->markdown('emails.admin.password')->with([
			'user'     => $this->user,
			'password' => $this->password,
		]);
	}
}
