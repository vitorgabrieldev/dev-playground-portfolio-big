@component('mail::message')

<h1 style="text-align:center;">Seu código de acesso é <br><b style="color:#d7ae5c; background:#f8f8f8;">{{ $code }}</b></h1>

Ficou com dúvidas? Fale com a gente escrevendo para o {{ config('general.email_support') }}.

@endcomponent