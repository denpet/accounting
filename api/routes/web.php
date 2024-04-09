<?php

use App\Http\Controllers\Unicenta\Report\StatementOfAccountController;
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


Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::group(['prefix' => 'unicenta'], function () {
        Route::group(['prefix' => 'reports'], function () {
            Route::get('statement-of-account/{id}', [StatementOfAccountController::class, 'show']);
        });
    });
});

//Route::view('{any}', 'app')->where('any', '.*');
