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
    Route::group([ 'prefix' => 'auth'], function () {
        Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth:api');
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh',[AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
    Route::group([ 'prefix' => 'posts'], function () {
        Route::get('/', [PostController::class, 'index'])->withoutMiddleware('auth:api');
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{post}',[PostController::class, 'show'])->withoutMiddleware('auth:api');
        Route::put('/{post}', [PostController::class, 'update']);
        Route::delete('/{post}', [PostController::class, 'destroy']);
    });
    Route::group([ 'prefix' => 'comments'], function () {
        Route::get('/', [CommentController::class, 'index'])->withoutMiddleware('auth:api');
        Route::post('/', [CommentController::class, 'store'])->withoutMiddleware('auth:api');
        Route::get('/{comment}',[CommentController::class, 'show'])->withoutMiddleware('auth:api');
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::put('/moderate/{comment}', [CommentController::class, 'moderate']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });


});
