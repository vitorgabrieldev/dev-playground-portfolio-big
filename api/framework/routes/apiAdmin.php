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
Route::post('auth/password/recovery', 'AuthController@passwordRecovery');
Route::post('auth/password/reset', 'AuthController@passwordRecoveryReset');

/******************************
 * Logged routers
 ******************************/
Route::middleware(['auth:users'])->group(function () {
	/******************************
	 * General
	 ******************************/
	// Auth
	Route::delete('auth/logout', 'AuthController@logout');
	Route::get('auth/user', 'AuthController@show');
	Route::get('auth/tokens', 'AuthController@tokens');
	Route::delete('auth/tokens/{tokenId}', 'AuthController@tokenDelete');
	Route::post('auth/change-password', 'AuthController@changePassword');
	Route::post('auth/change-avatar', 'AuthController@changeAvatar');

	// Dashboard
	Route::get('dashboard', 'DashboardController@index');

	// Log
	Route::get('logs', 'LogController@index')->middleware('permission:log.list');
	Route::get('logs/export', 'LogController@export')->middleware('permission:log.export');
	Route::get('logs/{log:uuid}', 'LogController@show')->middleware('permission:log.show');

	// Permissions
	Route::get('permissions/autocomplete', 'PermissionsController@autocomplete');

	// Rich text editor
	Route::post('rich-text-editor/upload-image', 'RichTextEditorController@uploadImage');

	// Roles
	Route::get('roles', 'RolesController@index')->middleware('permission:roles.list');
	Route::post('roles', 'RolesController@store')->middleware('permission:roles.create');
	Route::get('roles/autocomplete', 'RolesController@autocomplete');
	Route::get('roles/export', 'RolesController@export')->middleware('permission:roles.export');
	Route::get('roles/{role:uuid}', 'RolesController@show')->middleware('permission:roles.show');
	Route::post('roles/{role:uuid}', 'RolesController@update')->middleware('permission:roles.edit');
	Route::delete('roles/{role:uuid}', 'RolesController@destroy')->middleware('permission:roles.delete');

	// System log
	Route::get('system-log', 'SystemLogController@index')->middleware('permission:system-log.list');
	Route::get('system-log/export', 'SystemLogController@export')->middleware('permission:system-log.export');
	Route::get('system-log/{log:uuid}', 'SystemLogController@show')->middleware('permission:system-log.show');

	/******************************
	 * Users
	 ******************************/
	// Users
	Route::get('users', 'UsersController@index')->middleware('permission:users.list');
	Route::post('users', 'UsersController@store')->middleware('permission:users.create');
	Route::get('users/autocomplete', 'UsersController@autocomplete');
	Route::get('users/export', 'UsersController@export')->middleware('permission:users.export');
	Route::get('users/{user:uuid}', 'UsersController@show')->middleware('permission:users.show');
	Route::post('users/{user:uuid}', 'UsersController@update')->middleware('permission:users.edit');
	Route::delete('users/{user:uuid}', 'UsersController@destroy')->middleware('permission:users.delete');

	/******************************
	 * Settings
	 ******************************/
	// General
	Route::get('settings/general', 'SettingsGeneralController@index')->middleware('permission:settings-general.list');
	Route::post('settings/general', 'SettingsGeneralController@update')->middleware('permission:settings-general.edit');

	// Notifications
	Route::get('settings/notifications', 'SettingsNotificationsController@index')->middleware('permission:settings-notifications.list');
	Route::post('settings/notifications', 'SettingsNotificationsController@update')->middleware('permission:settings-notifications.edit');

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
	 * Push's
	 ******************************/
	// General
	Route::get('push-general', 'PushGeneralController@index')->middleware('permission:push-general.list');
	Route::post('push-general', 'PushGeneralController@store')->middleware('permission:push-general.create');
	Route::post('push-general/{push:uuid}', 'PushGeneralController@update')->middleware('permission:push-general.edit');
	Route::get('push-general/export', 'PushGeneralController@export')->middleware('permission:push-general.export');
	Route::get('push-general/{push:uuid}', 'PushGeneralController@show')->middleware('permission:push-general.show');
	Route::delete('push-general/{push:uuid}', 'PushGeneralController@destroy')->middleware('permission:push-general.delete');

	// City
	Route::get('push-city', 'PushCityController@index')->middleware('permission:push-city.list');
	Route::post('push-city', 'PushCityController@store')->middleware('permission:push-city.create');
	Route::post('push-city/{push:uuid}', 'PushCityController@update')->middleware('permission:push-city.edit');
	Route::get('push-city/export', 'PushCityController@export')->middleware('permission:push-city.export');
	Route::get('push-city/{push:uuid}', 'PushCityController@show')->middleware('permission:push-city.show');
	Route::delete('push-city/{push:uuid}', 'PushCityController@destroy')->middleware('permission:push-city.delete');

	// State
	Route::get('push-state', 'PushStateController@index')->middleware('permission:push-state.list');
	Route::post('push-state', 'PushStateController@store')->middleware('permission:push-state.create');
	Route::post('push-state/{push:uuid}', 'PushStateController@update')->middleware('permission:push-state.edit');
	Route::get('push-state/export', 'PushStateController@export')->middleware('permission:push-state.export');
	Route::get('push-state/{push:uuid}', 'PushStateController@show')->middleware('permission:push-state.show');
	Route::delete('push-state/{push:uuid}', 'PushStateController@destroy')->middleware('permission:push-state.delete');

	// User
	Route::get('push-user', 'PushUserController@index')->middleware('permission:push-user.list');
	Route::post('push-user', 'PushUserController@store')->middleware('permission:push-user.create');
	Route::post('push-user/{push:uuid}', 'PushUserController@update')->middleware('permission:push-user.edit');
	Route::get('push-user/export', 'PushUserController@export')->middleware('permission:push-user.export');
	Route::get('push-user/{push:uuid}', 'PushUserController@show')->middleware('permission:push-user.show');
	Route::delete('push-user/{push:uuid}', 'PushUserController@destroy')->middleware('permission:push-user.delete');

	/******************************
	 * Institucional Contents
	 ******************************/
	// Privacy policy
	Route::get('privacy-policy', 'PrivacyPolicyController@index')->middleware('permission:privacy-policy.edit');
	Route::post('privacy-policy', 'PrivacyPolicyController@update')->middleware('permission:privacy-policy.edit');

	// grupo AIZ
	Route::get('grupo-aiz', 'GrupoAizController@index')->middleware('permission:grupo-aiz.edit');
	Route::post('grupo-aiz', 'GrupoAizController@update')->middleware('permission:grupo-aiz.edit');

	// Terms of use
	Route::get('terms-of-use', 'TermsOfUseController@index')->middleware('permission:terms-of-use.edit');
	Route::post('terms-of-use', 'TermsOfUseController@update')->middleware('permission:terms-of-use.edit');

	/******************************
	 * Contents
	 ******************************/
	// Faq
	Route::get('faq', 'FaqController@index')->middleware('permission:faq.list');
	Route::post('faq', 'FaqController@store')->middleware('permission:faq.create');
	Route::get('faq/additional-information', 'FaqController@additionalInformation');
	Route::get('faq/export', 'FaqController@export')->middleware('permission:faq.export');
	Route::get('faq/{faq:uuid}', 'FaqController@show')->middleware('permission:faq.show');
	Route::post('faq/{faq:uuid}', 'FaqController@update')->middleware('permission:faq.edit');
	Route::delete('faq/{faq:uuid}', 'FaqController@destroy')->middleware('permission:faq.delete');

	// Categorias Treinamento
	Route::get('categorias-treinamento', 'CategoriasTreinamentoController@index')->middleware('permission:categorias_treinamento.list');
	Route::post('categorias-treinamento', 'CategoriasTreinamentoController@store')->middleware('permission:categorias_treinamento.create');
	Route::get('categorias-treinamento/autocomplete', 'CategoriasTreinamentoController@autocomplete');
	Route::get('categorias-treinamento/additional-information', 'CategoriasTreinamentoController@additionalInformation');
	Route::get('categorias-treinamento/export', 'CategoriasTreinamentoController@export')->middleware('permission:categorias_treinamento.export');
	Route::get('categorias-treinamento/{categoria:uuid}', 'CategoriasTreinamentoController@show')->middleware('permission:categorias_treinamento.show');
	Route::post('categorias-treinamento/{categoria:uuid}', 'CategoriasTreinamentoController@update')->middleware('permission:categorias_treinamento.edit');
	Route::delete('categorias-treinamento/{categoria:uuid}', 'CategoriasTreinamentoController@destroy')->middleware('permission:categorias_treinamento.delete');

	// Treinamento
	Route::get('treinamento', 'TreinamentoController@index')->middleware('permission:treinamento.list');
	Route::post('treinamento', 'TreinamentoController@store')->middleware('permission:treinamento.create');
	Route::get('treinamento/additional-information', 'TreinamentoController@additionalInformation');
	Route::get('treinamento/export', 'TreinamentoController@export')->middleware('permission:treinamento.export');
	Route::get('treinamento/{treinamento:uuid}', 'TreinamentoController@show')->middleware('permission:treinamento.show');
	Route::post('treinamento/{treinamento:uuid}', 'TreinamentoController@update')->middleware('permission:treinamento.edit');
	Route::delete('treinamento/{treinamento:uuid}', 'TreinamentoController@destroy')->middleware('permission:treinamento.delete');

	// Manuais
	Route::get('manuais', 'ManuaisController@index')->middleware('permission:manuais.list');
	Route::post('manuais', 'ManuaisController@store')->middleware('permission:manuais.create');
	Route::get('manuais/additional-information', 'ManuaisController@additionalInformation');
	Route::get('manuais/export', 'ManuaisController@export')->middleware('permission:manuais.export');
	Route::get('manuais/{manual:uuid}', 'ManuaisController@show')->middleware('permission:manuais.show');
	Route::post('manuais/{manual:uuid}', 'ManuaisController@update')->middleware('permission:manuais.edit');
	Route::delete('manuais/{manual:uuid}', 'ManuaisController@destroy')->middleware('permission:manuais.delete');

	// ProtetoresTela
	Route::get('protetores-tela', 'ProtetoresTelaController@index')->middleware('permission:protetores_tela.list');
	Route::post('protetores-tela', 'ProtetoresTelaController@store')->middleware('permission:protetores_tela.create');
	Route::get('protetores-tela/additional-information', 'ProtetoresTelaController@additionalInformation');
	Route::get('protetores-tela/export', 'ProtetoresTelaController@export')->middleware('permission:protetores_tela.export');
	Route::get('protetores-tela/{protetortela:uuid}', 'ProtetoresTelaController@show')->middleware('permission:protetores_tela.show');
	Route::post('protetores-tela/{protetortela:uuid}', 'ProtetoresTelaController@update')->middleware('permission:protetores_tela.edit');
	Route::delete('protetores-tela/{protetortela:uuid}', 'ProtetoresTelaController@destroy')->middleware('permission:protetores_tela.delete');

	// ver mais
	Route::get('ver-mais', 'VerMaisController@index')->middleware('permission:ver_mais.list');
	Route::post('ver-mais', 'VerMaisController@store')->middleware('permission:ver_mais.create');
	Route::get('ver-mais/additional-information', 'VerMaisController@additionalInformation');
	Route::get('ver-mais/export', 'VerMaisController@export')->middleware('permission:ver_mais.export');
	Route::get('ver-mais/{ver_mais:uuid}', 'VerMaisController@show')->middleware('permission:ver_mais.show');
	Route::post('ver-mais/{ver_mais:uuid}', 'VerMaisController@update')->middleware('permission:ver_mais.edit');
	Route::delete('ver-mais/{ver_mais:uuid}', 'VerMaisController@destroy')->middleware('permission:ver_mais.delete');

	// vale do rio doce
	Route::get('vale-rio-doce', 'ValeRioDoceController@index')->middleware('permission:vale_rio_doce.list');
	Route::post('vale-rio-doce', 'ValeRioDoceController@store')->middleware('permission:vale_rio_doce.create');
	Route::get('vale-rio-doce/additional-information', 'ValeRioDoceController@additionalInformation');
	Route::get('vale-rio-doce/export', 'ValeRioDoceController@export')->middleware('permission:vale_rio_doce.export');
	Route::get('vale-rio-doce/{vale_rio_doce:uuid}', 'ValeRioDoceController@show')->middleware('permission:vale_rio_doce.show');
	Route::post('vale-rio-doce/{vale_rio_doce:uuid}', 'ValeRioDoceController@update')->middleware('permission:vale_rio_doce.edit');
	Route::delete('vale-rio-doce/{vale_rio_doce:uuid}', 'ValeRioDoceController@destroy')->middleware('permission:vale_rio_doce.delete');

	// Banners
	Route::get('banners', 'BannersController@index')->middleware('permission:banners.list');
	Route::post('banners', 'BannersController@store')->middleware('permission:banners.create');
	Route::get('banners/additional-information', 'BannersController@additionalInformation');
	Route::get('banners/export', 'BannersController@export')->middleware('permission:banners.export');
	Route::get('banners/{banners:uuid}', 'BannersController@show')->middleware('permission:banners.show');
	Route::post('banners/{banners:uuid}', 'BannersController@update')->middleware('permission:banners.edit');
	Route::delete('banners/{banners:uuid}', 'BannersController@destroy')->middleware('permission:banners.delete');

	// PerfisAcesso
	Route::get('perfis-acesso', 'PerfisAcessoController@index')->middleware('permission:perfil_acesso.list');
	Route::post('perfis-acesso', 'PerfisAcessoController@store')->middleware('permission:perfil_acesso.create');
	Route::get('perfis-acesso/autocomplete', 'PerfisAcessoController@autocomplete');
	Route::get('perfis-acesso/additional-information', 'PerfisAcessoController@additionalInformation');
	Route::get('perfis-acesso/export', 'PerfisAcessoController@export')->middleware('permission:perfil_acesso.export');
	Route::get('perfis-acesso/{perfil:uuid}', 'PerfisAcessoController@show')->middleware('permission:perfil_acesso.show');
	Route::post('perfis-acesso/{perfil:uuid}', 'PerfisAcessoController@update')->middleware('permission:perfil_acesso.edit');
	Route::delete('perfis-acesso/{perfil:uuid}', 'PerfisAcessoController@destroy')->middleware('permission:perfil_acesso.delete');

	// Preferencias
	Route::get('preferencias', 'PreferenciasController@index')->middleware('permission:preferencias.list');
	Route::post('preferencias', 'PreferenciasController@store')->middleware('permission:preferencias.create');
	Route::get('preferencias/autocomplete', 'PreferenciasController@autocomplete');
	Route::get('preferencias/additional-information', 'PreferenciasController@additionalInformation');
	Route::get('preferencias/export', 'PreferenciasController@export')->middleware('permission:preferencias.export');
	Route::get('preferencias/{preferencia:uuid}', 'PreferenciasController@show')->middleware('permission:preferencias.show');
	Route::post('preferencias/{preferencia:uuid}', 'PreferenciasController@update')->middleware('permission:preferencias.edit');
	Route::delete('preferencias/{preferencia:uuid}', 'PreferenciasController@destroy')->middleware('permission:preferencias.delete');

	// Notifications
	Route::get('notifications', 'NotificationsController@index')->middleware('permission:notifications.list');
	Route::post('notifications', 'NotificationsController@store')->middleware('permission:notifications.create');
	Route::get('notifications/export', 'NotificationsController@export')->middleware('permission:notifications.export');
	Route::get('notifications/{notification:uuid}', 'NotificationsController@show')->middleware('permission:notifications.show');
	Route::post('notifications/{notification:uuid}', 'NotificationsController@update')->middleware('permission:notifications.edit');
	Route::delete('notifications/{notification:uuid}', 'NotificationsController@destroy')->middleware('permission:notifications.delete');

	// customer
	Route::get('customers', 'CustomerController@index')->middleware('permission:customers.list');
	Route::get('customers/autocomplete', 'CustomerController@autocomplete');
	Route::post('customers', 'CustomerController@store')->middleware('permission:customers.create');
	Route::post('customers/change-avatar/{customer:uuid}', 'CustomerController@changeAvatar')->middleware('permission:customers.edit');
	Route::post('customers/update/{customer:uuid}', 'CustomerController@update')->middleware('permission:customers.edit');
	Route::get('customers/export', 'CustomerController@export')->middleware('permission:customers.export');
	Route::get('customers/{customer:uuid}', 'CustomerController@show')->middleware('permission:customers.show');
	Route::delete('customers/{customer:uuid}', 'CustomerController@destroy')->middleware('permission:customers.delete');
	Route::post('customers/active-desactive/{customer:uuid}', 'CustomerController@activeDisabled')->middleware('permission:customers.edit');

	// customer excluidos
	Route::get('customers-deleted', 'CustomerController@indexDeleted')->middleware('permission:customers-deleted.list');
	Route::get('customers-deleted/export', 'CustomerController@exportDeleted')->middleware('permission:customers-deleted.export');
	Route::get('customers-deleted/{uuid}', 'CustomerController@showDeleted')->middleware('permission:customers-deleted.show');

	// Onboarding
	Route::get('onboarding', 'OnboardingController@index')->middleware('permission:onboarding.show');
	Route::post('onboarding', 'OnboardingController@update')->middleware('permission:onboarding.edit');

	// News
	Route::get('news', 'NewsController@index')->middleware('permission:news.list');
	Route::post('news', 'NewsController@store')->middleware('permission:news.create');
	Route::get('news/export', 'NewsController@export')->middleware('permission:news.export');
	Route::get('news/{new:uuid}', 'NewsController@show')->middleware('permission:news.show');
	Route::post('news/{new:uuid}', 'NewsController@update')->middleware('permission:news.edit');
	Route::delete('news/{new:uuid}', 'NewsController@destroy')->middleware('permission:news.delete');
});
