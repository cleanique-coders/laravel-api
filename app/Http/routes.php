<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group([
	'middleware' => 'api',
	'prefix' => 'api', 
	'namespace' => '\Api\V1'
	],function(){

	Route::group(['prefix' => 'v1'],function(){
		Route::post('/login','Auth\AuthenticateController@login');
		Route::post('/signup','Auth\AuthenticateController@signup');
	});
});
