@component('mail::message')

Olá {{ $user->name }},<br>

Bem-vindo ao {{ config('app.name_short') }} !

@component('mail::button', ['url' => url('email/verify', $token)])
	Verificar e-mail
@endcomponent

Ficou com dúvidas? Fale com a gente escrevendo para o {{ config('general.email_support') }}.

E-mail automático, não responda.
@endcomponent