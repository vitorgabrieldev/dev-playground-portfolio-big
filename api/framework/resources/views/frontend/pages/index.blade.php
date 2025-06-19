<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" prefix="og: http://ogp.me/ns#">
<head>
	<base href="{{ config('app.url') }}/">
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

	<title>{{ $seo_title }}</title>

	<meta name="robots" content="index, follow">
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
	<meta name="format-detection" content="telephone=no">
	<meta name="msapplication-tap-highlight" content="no">
	<meta name="theme-color" content="#6a3bc2" />

	<link rel="apple-touch-icon" href="{{ asset('images/frontend/logos/57x57.png') }}">
	<link rel="apple-touch-icon" sizes="76x76" href="{{ asset('images/frontend/logos/76x76.png') }}">
	<link rel="apple-touch-icon" sizes="120x120" href="{{ asset('images/frontend/logos/120x120.png') }}">
	<link rel="apple-touch-icon" sizes="152x152" href="{{ asset('images/frontend/logos/152x152.png') }}">
	<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/frontend/logos/180x180.png') }}">
	<link rel="icon" sizes="192x192" href="{{ asset('images/frontend/logos/192x192.png') }}">
	<link rel="shortcut icon" type="image/png" href="{{ asset('images/frontend/logos/32x32.png') }}">

	<meta name="application-name" content="{{ config('app.name_short') }}">
	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="{{ asset('images/frontend/logos/180x180.png') }}">
	<meta name="msapplication-config" content="none">

	<meta name="description" content="{{ $seo_description }}">
	<link rel="canonical" href="{{ $seo_url }}">

	<link href="{{ is_environment_local() ? asset('/css/frontend/antd.css') : mix('/css/frontend/antd.css') }}" rel="stylesheet" type="text/css">
	<link href="{{ is_environment_local() ? asset('/css/frontend/app.css') : mix('/css/frontend/app.css') }}" rel="stylesheet" type="text/css">

	<link rel="preload" as="font" crossorigin="anonymous" type="font/woff2" href="fonts/frontend/roboto/roboto-regular.woff2" />
	<link rel="preload" as="font" crossorigin="anonymous" type="font/woff2" href="fonts/frontend/roboto/roboto-bold.woff2" />
	<link rel="preload" as="font" crossorigin="anonymous" type="font/woff2" href="fonts/frontend/roboto/roboto-black.woff2" />
</head>
<body>
<div id="root"></div>
<script>
	window.config      = @json(site_default_config());
	// Disable auto zensmooth
	window.noZensmooth = true;
</script>
<script src="{{ is_environment_local() ? asset('/js/frontend/app.js') : mix('/js/frontend/app.js') }}"></script>
</body>
</html>
