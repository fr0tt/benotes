<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


Route::post('api/auth/login', 'AuthController@login');
Route::post('api/auth/register', 'AuthController@register');

Route::group([
    'middleware' => 'auth'
], function () {

    Route::get('api/auth/me', 'AuthController@me');
    Route::post('api/auth/refresh', 'AuthController@refresh');
    Route::post('api/auth/logout', 'AuthController@logout');

    Route::get('api/posts', 'PostController@index');
    Route::post('api/posts', 'PostController@store');

}); 


Route::get('/{any:.*}', function () {
    return view('app');
});