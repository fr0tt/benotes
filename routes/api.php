<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
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

Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/refresh', [AuthController::class, 'refresh']);
Route::post('auth/forgot', [AuthController::class, 'sendReset']);
Route::post('auth/reset', [AuthController::class, 'reset']);

Route::group([
    'middleware' => 'auth:api' //'auth:sanctum'
], function () {

    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    Route::post('posts', [PostController::class, 'store']);
    Route::patch('posts/{id}', [PostController::class, 'update']);
    Route::delete('posts/{id}', [PostController::class, 'destroy']);

    Route::get('meta', [PostController::class, 'getUrlInfo']);

    Route::get('tags', [TagController::class, 'index']);
    Route::get('tags/{id}', [TagController::class, 'show']);
    Route::post('tags', [TagController::class, 'store']);
    Route::patch('tags/{id}', [TagController::class, 'update']);
    Route::delete('tags/{id}', [TagController::class, 'destroy']);

    Route::get('collections', [CollectionController::class, 'index']);
    Route::get('collections/{id}', [CollectionController::class, 'show']);
    Route::post('collections', [CollectionController::class, 'store']);
    Route::patch('collections/{id}', [CollectionController::class, 'update']);
    Route::delete('collections/{id}', [CollectionController::class, 'destroy']);

    Route::get('users', [UserController::class, 'index']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::post('users', [UserController::class, 'store']);
    Route::patch('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);

    Route::get('shares', [ShareController::class, 'index']);
    Route::post('shares', [ShareController::class, 'store']);
    Route::patch('shares/{id}', [ShareController::class, 'update']);
    Route::delete('shares/{id}', [ShareController::class, 'destroy']);

    Route::post('files', [FileController::class, 'store']);

    Route::post('imports', [ImportController::class, 'store']);
    Route::get('exports', [ExportController::class, 'index']);

});

Route::group([
    'middleware' => 'auth:share,api' // ,auth:sanctum
], function () {
    Route::get('posts', [PostController::class, 'index']);
    Route::get('posts/{id}', [PostController::class, 'show']);

    Route::get('shares/me', [ShareController::class, 'me']);
});



Route::post('__e2e__/user', [TestingController::class, 'user']);
Route::post('__e2e__/setup', [TestingController::class, 'setup']);
Route::post('__e2e__/teardown', [TestingController::class, 'teardown']);
