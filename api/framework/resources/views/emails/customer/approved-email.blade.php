@component('mail::message')

Olá {{ $user->name }},<br>

Bem-vindo ao {{ config('app.name_short') }} !

Sua conta foi aprovada com sucesso, agora você pode acessar o nosso aplicativo e começar a utilizar nossos serviços.

@component('mail::button', ['url' => 'meuapp://'])
	Acessar o aplicativo
@endcomponent

Ficou com dúvidas? Fale com a gente escrevendo para o {{ config('general.email_support') }}.

E-mail automático, não responda.
@endcomponent