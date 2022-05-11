<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use Illuminate\Http\Request;
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

Route::group(['middleware' => 'auth:api'], function () {
    Route::group(['prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth:api');
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });

    Route::apiResource('posts', PostController::class)->only(['index', 'show'])
        ->withoutMiddleware('auth:api');
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);

    Route::apiResource('comments', CommentController::class)->only(['index', 'show', 'store'])
        ->withoutMiddleware('auth:api');
    Route::apiResource('comments', CommentController::class)->except(['index', 'show', 'store']);
    Route::put('comments/moderate/{comment}', [CommentController::class, 'moderate'])->name('comments.moderate');


});
