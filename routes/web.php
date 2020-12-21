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
Route::post('api/auth/refresh', 'AuthController@refresh');

Route::group([
    'middleware' => 'auth'
], function () {

    Route::get('api/auth/me', 'AuthController@me');
    Route::post('api/auth/logout', 'AuthController@logout');

    Route::post('api/posts', 'PostController@store');
    Route::patch('api/posts/{id}', 'PostController@update');
    Route::delete('api/posts/{id}', 'PostController@destroy');

    Route::get('api/collections', 'CollectionController@index');
    Route::get('api/collections/{id}', 'CollectionController@show');
    Route::post('api/collections', 'CollectionController@store');
    Route::patch('api/collections/{id}', 'CollectionController@update');
    Route::delete('api/collections/{id}', 'CollectionController@destroy');
    
    Route::get('api/users', 'UserController@index');
    Route::get('api/users/{id}', 'UserController@show');
    Route::post('api/users', 'UserController@store');
    Route::patch('api/users/{id}', 'UserController@update');
    Route::delete('api/users/{id}', 'UserController@destroy');

    Route::get('api/share', 'ShareController@index');
    Route::post('api/share', 'ShareController@store');
    Route::patch('api/share/{id}', 'ShareController@update');
    Route::delete('api/share/{id}', 'ShareController@destroy');

}); 

Route::group([
    'middleware' => 'auth:share'
], function () {
    Route::get('api/posts', 'PostController@index');
    Route::get('api/posts/{id}', 'PostController@show');
    
    Route::get('api/share/me', 'ShareController@me');
});


Route::get('/{any:.*}', function () {
    return view('app');
});