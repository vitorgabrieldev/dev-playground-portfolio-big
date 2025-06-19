<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Name of route
	|--------------------------------------------------------------------------
	|
	| Enter the routes name to enable dynamic imagecache manipulation.
	| This handle will define the first part of the URI:
	|
	| {route}/{template}/{filename}
	|
	| Examples: "images", "img/cache"
	|
	*/

	'route' => 'cache-images',

	/*
	|--------------------------------------------------------------------------
	| Storage paths
	|--------------------------------------------------------------------------
	|
	| The following paths will be searched for the image filename, submitted
	| by URI.
	|
	| Define as many directories as you like.
	|
	*/

	'paths' => [
		public_path('storage'),
	],

	/*
	|--------------------------------------------------------------------------
	| Manipulation templates
	|--------------------------------------------------------------------------
	|
	| Here you may specify your own manipulation filter templates.
	| The keys of this array will define which templates
	| are available in the URI:
	|
	| {route}/{template}/{filename}
	|
	| The values of this array will define which filter class
	| will be applied, by its fully qualified name.
	|
	*/

	'templates' => [
		// Admin
		'admin_listing'        => App\Images\AdminListing::class,
		'admin_listing_medium' => App\Images\AdminListingMedium::class,
		// General
		'preloader'            => App\Images\Preloader::class,
		// Front
		'app_listing'          => App\Images\AppListing::class,
		'app_detail'           => App\Images\AppDetail::class,
	],

	/*
	|--------------------------------------------------------------------------
	| Image Cache Lifetime
	|--------------------------------------------------------------------------
	|
	| Lifetime in minutes of the images handled by the imagecache route.
	|
	*/

	'lifetime' => 1051200,

];
