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
Route::post('api/auth/refresh', 'AuthController@refresh');

Route::group([
    'middleware' => 'auth'
], function () {

    Route::get('api/auth/me', 'AuthController@me');
    Route::post('api/auth/logout', 'AuthController@logout');

    Route::get('api/posts', 'PostController@index');
    Route::post('api/posts', 'PostController@store');
    Route::delete('api/posts/{id}', 'PostController@destroy');

    Route::get('api/collections', 'CollectionController@index');
    Route::get('api/collections/{id}', 'CollectionController@show');
    Route::post('api/collections', 'CollectionController@store');
    Route::delete('api/collections/{id}', 'CollectionController@destroy');

}); 


Route::get('/{any:.*}', function () {
    return view('app');
});