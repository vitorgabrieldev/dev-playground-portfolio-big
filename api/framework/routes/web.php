<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Password reset
Route::get('password/reset/{type}/{token}', 'IndexController@index')->name('password.reset');
Route::post('password/reset/{type}', 'IndexController@index')->name('password.update');
Route::get('password/reset-success/{type}', 'IndexController@index')->name('password.success');

// verify account
Route::get('email/verify/{token}', 'IndexController@index');

// Default
Route::get('/{any?}', 'IndexController@index')->where('any', '.*')->middleware('disable_on_json_request');
