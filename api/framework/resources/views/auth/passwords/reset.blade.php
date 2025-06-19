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
				@if( $errors->any() )
					<div class="uk-alert-danger" uk-alert>
						<a class="uk-alert-close" uk-close></a>
						@foreach( $errors->all() as $error )
							<p>{{ $error }}</p>
						@endforeach
					</div>
				@endif

				<form class="uk-form-stacked" method="post" action="{{ route('password.update', $type) }}" data-validate autocomplete="off">
					@csrf
					<input type="hidden" name="token" value="{{ $token }}">
					<div class="uk-margin uk-hidden">
						<label class="uk-form-label">Usuário</label>
						<div class="uk-form-controls">
							<input type="text" name="email" class="uk-input uk-border-rounded" value="{{ $email ?? old('email') }}" readonly>
						</div>
					</div>
					<div class="uk-margin">
						<label class="uk-form-label">{{ __('Password') }}</label>
						<div class="uk-form-controls">
							<input type="password" name="password" class="uk-input uk-border-rounded" minlength="6" required>
						</div>
					</div>
					<div class="uk-margin">
						<label class="uk-form-label">{{ __('Confirm Password') }}</label>
						<div class="uk-form-controls">
							<input type="password" name="password_confirmation" class="uk-input uk-border-rounded" minlength="6" required>
						</div>
					</div>
					<button type="submit" class="uk-button uk-width-1-1 uk-button-primary uk-border-pill btn-send">{{ __('Reset Password') }}</button>
				</form>

			</article>
		</div>
	</div>

</main>

<script src="{{ is_environment_local() ? asset('/js/frontend/app.js') : mix('/js/frontend/app.js') }}"></script>
</body>
</html>
