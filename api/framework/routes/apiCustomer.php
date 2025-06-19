<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/******************************
 * General
 ******************************/
// Auth
Route::post('auth/login', 'AuthController@login');
Route::post('auth/send-code', 'AuthController@sendCode');
Route::post('auth/validate-code', 'AuthController@validateCode');

// auth external
Route::post('auth/login-external', 'AuthController@loginExternal');


Route::post('auth/password/recovery', 'AuthController@passwordRecovery');
Route::post('auth/password/reset', 'AuthController@passwordRecoveryReset');

/******************************
 * Webservice
 ******************************/
// Cities
Route::get('webservice/cities', 'WebserviceController@cities');

// States
Route::get('webservice/states', 'WebserviceController@states');

// Zipcode
Route::get('webservice/zipcode/{zipcode}', 'WebserviceController@zipcode');

/******************************
 * Institucional Contents
 ******************************/
// Privacy policy
Route::get('institutional/privacy-policy', 'InstitutionalController@privacyPolicy');

// Terms of use
Route::get('institutional/terms-of-use', 'InstitutionalController@termsOfUse');

// Grupo AIZ
Route::get('institutional/grupo-aiz', 'InstitutionalController@grupoAiz');

/******************************
 * Contents
 ******************************/
// Faq
Route::get('faq', 'FaqController@index');
Route::get('faq/{faq:uuid}', 'FaqController@show');

// Conteudo ver mais
Route::get('ver-mais', 'VerMaisController@index');
Route::get('ver-mais/{ver_mais:uuid}', 'VerMaisController@show');

// Onboarding
Route::get('onboarding', 'OnboardingController@index');

// Customer
Route::post('customer/create', 'AuthController@store');
Route::post('customer/email/verify', 'AuthController@verifyEmail');
Route::post('customer/email/resend', 'AuthController@resendVerificationEmail');

// Perfis de acesso
Route::get('perfis-acesso', 'PerfisAcessoController@index');
Route::get('perfis-acesso/{perfil:uuid}', 'PerfisAcessoController@show');

// Banners
Route::get('banners', 'BannersController@index');
Route::get('banners/{banners:uuid}', 'BannersController@show');

/******************************
 * Settings
 ******************************/
// General
Route::get('settings/general', 'SettingsGeneralController@index');

/******************************
 * Rotas para usuários
 ******************************/
Route::middleware(['auth:customers,socialnetwork_customers'])->group(function () {

	/******************************
	 * General
	 ******************************/
	// Auth
	Route::delete('auth/logout', 'AuthController@logout');
	Route::get('auth/user', 'AuthController@show');
	Route::get('auth/tokens', 'AuthController@tokens');
	Route::delete('auth/tokens/{tokenId}', 'AuthController@tokenDelete');
	Route::post('auth/delete-account', 'AuthController@deleteAccount');
	Route::post('auth/change-password', 'AuthController@changePassword');
	Route::post('auth/update/notify', 'AuthController@settingsNotify');
	Route::post('auth/update', 'AuthController@update');
	Route::post('auth/change-avatar', 'AuthController@changeAvatar');

	// Manuais
	Route::get('manuais', 'ManuaisController@index');
	Route::get('manuais/{manual:uuid}', 'ManuaisController@show');

	// News
	Route::get('news', 'NewsController@index');
	Route::get('news/{new:uuid}', 'NewsController@show');

	// notifications
	Route::get('notifications', 'NotificationsController@index');
	Route::post('notifications/read/{notification:uuid}', 'NotificationsController@read');
    Route::delete('notifications/destroy-all', 'NotificationsController@destroyAll');
	Route::delete('notifications/read-all', 'NotificationsController@readAll');
	Route::get('notifications/{notification:uuid}', 'NotificationsController@show');
	Route::delete('notifications/{notification:uuid}', 'NotificationsController@destroy');

	// ProtetoresTela
	Route::get('protetores-tela', 'ProtetoresTelaController@index');
	Route::get('protetores-tela/{protetortela:uuid}', 'ProtetoresTelaController@show');

	// Treinamento
	Route::get('treinamento', 'TreinamentoController@index');
	Route::get('treinamento/{treinamento:uuid}', 'TreinamentoController@show');
	Route::post('treinamento/read/{treinamento:uuid}', 'TreinamentoController@read');
	Route::post('treinamento/favorite/{treinamento:uuid}', 'TreinamentoController@favorite');

	// vale do rio doce
	Route::get('vale-rio-doce', 'ValeRioDoceController@index');
	Route::get('vale-rio-doce/{vale_rio_doce:uuid}', 'ValeRioDoceController@show');
});