@component('mail::message')

Olá {{ $user->name }},<br>

Você foi cadastrado como administrador em {{ config('app.name_short') }}.

Acesse com o login e senha abaixo:<br>
<p>
	Login: {{ $user->email }}
	<br>
	Senha: {{ $password }}
</p>

@component('mail::button', ['url' => config('app.url_admin')])
	Acessar
@endcomponent

Um detalhe, ao fazer o primeiro acesso não esqueça de alterar sua senha, recomendamos sempre insira uma senha que se lembre com facilidade.

Ficou com dúvidas? Fale com a gente escrevendo para o {{ config('general.email_support') }}.

@endcomponent
