@component('mail::message')

Um usuário deletou a sua conta em {{ config('app.name_short') }}.<br>

Resumo do usuário:<br>
<p>
	Nome: {{ $user->name }}
	<br>
	E-mail: {{ $user->email }}
	<br>
	Documento: {{ $user->document }}
	<br>
	Contato: {{ $user->phone }}
	<br>
</p>

@component('mail::button', ['url' => config('app.url_admin')])
	Acessar Painel Admin
@endcomponent

Ficou com dúvidas? Fale com a gente escrevendo para o {{ config('general.email_support') }}.

@endcomponent
