<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\TransactionController;
use App\Http\Controllers\Global\CountryController;
use App\Http\Controllers\User\UserController;
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

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::group(['prefix' => 'accounting'], function () {
        Route::get('accounts/options', [AccountController::class, 'options']);
        Route::apiResource('accounts', AccountController::class);

        Route::post('transactions/upload', [TransactionController::class, 'uploadImage']);
        Route::get('transactions/image/{id}', [TransactionController::class, 'showImage']);
        Route::apiResource('transactions', TransactionController::class);
    });

    Route::group(['prefix' => 'global'], function () {
        Route::get('countries/options', [CountryController::class, 'options']);
        Route::apiResource('countries', CountryController::class);
    });

    Route::group(['prefix' => 'users'], function () {
        Route::get('users/options', [UserController::class, 'options']);
        Route::apiResource('users', UserController::class);
    });
});
