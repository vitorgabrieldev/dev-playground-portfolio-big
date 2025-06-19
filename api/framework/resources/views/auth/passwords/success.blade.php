<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<base href="{{ url('/') }}/">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name') }}</title>
	<meta name="description" content="">
	<meta name="keywords" content="">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="format-detection" content="telephone=no">
	<meta name="msapplication-tap-highlight" content="no">

	<link rel="shortcut icon" href="{{ asset('images/frontend/logos/32x32.png') }}" type="image/png">

	<link href="{{ is_environment_local() ? asset('/css/frontend/app.css') : mix('/css/frontend/app.css') }}" rel="stylesheet" type="text/css">
</head>
<body>

<main id="site-corpo" class="uk-padding-remove-top">

	<div class="uk-container">
		<div class="uk-grid uk-grid-small uk-flex-center" uk-grid>
			<div class="uk-margin-medium-bottom" style="padding-top:40px; width:270px;">
				<img src="{{ asset('images/frontend/logos/logo.svg') }}" alt="">
			</div>
		</div>
		<div class="uk-grid uk-grid-medium uk-flex-center" uk-grid>
			<article class="uk-width-1-4 coluna-conteudo">
				<header class="titulo-pagina">
					<h1 class="uk-text-center">{{ __('Reset Password') }}</h1>
				</header>
				<div class="uk-text-center">
					<p>{{ __('passwords.reset') }}</p>
					@if( $type )
						<a class="uk-button uk-button-primary uk-border-pill" href="{{ config('app.url_admin') }}">Ir para o painel</a>
					@endif
				</div>
			</article>
		</div>
	</div>

</main>

<script src="{{ is_environment_local() ? asset('/js/frontend/app.js') : mix('/js/frontend/app.js') }}"></script>
</body>
</html>
