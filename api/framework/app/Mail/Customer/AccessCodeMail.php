<?php

namespace App\Mail\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AccessCodeMail extends Mailable
{

	use Queueable, SerializesModels;

	/**
	 * Code
	 *
	 * @var string
	 */
	public string $code;

	/**
	 * Type
	 *
	 * @var string
	 */
	public string $type;

	/**
	 * Create a new message instance.
	 *
	 * @param string $code
	 * @param string $type
	 */
	public function __construct(string $code, string $type)
	{
		$this->code = $code;
		$this->type = $type;
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->subject('Seu código de acesso é ' . $this->code . ' - ' . config('app.name_short'))->markdown('emails.customer.access-code');
	}
}
