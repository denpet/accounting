<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\TransactionController;
use App\Http\Controllers\Global\CountryController;
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

        Route::apiResource('transactions', TransactionController::class);
    });

    Route::group(['prefix' => 'global'], function () {
        Route::get('countries/options', [CountryController::class, 'options']);
        Route::apiResource('countries', CountryController::class);
    });
});
