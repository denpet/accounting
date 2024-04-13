<?php

use App\Http\Controllers\Accounting\AccountController;
use App\Http\Controllers\Accounting\TransactionController;
use App\Http\Controllers\Global\CountryController;
use App\Http\Controllers\Payroll\EmployeeController;
use App\Http\Controllers\Payroll\TimeRecordController;
use App\Http\Controllers\Unicenta\CustomerController;
use App\Http\Controllers\Unicenta\Report\StatementOfAccountController;
use App\Http\Controllers\User\RoleController;
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
        Route::get('roles/options', [RoleController::class, 'options']);
        Route::apiResource('roles', RoleController::class);
    });

    Route::group(['prefix' => 'payroll'], function () {
        Route::get('employees/options', [EmployeeController::class, 'options']);
        Route::apiResource('employees', EmployeeController::class);

        Route::get('time-records/week-options', [TimeRecordController::class, 'weekOptions']);
        Route::get('time-records/{employeeId}/{weekOf}', [TimeRecordController::class, 'showEmployeeWeek']);
        Route::apiResource('time-records', TimeRecordController::class);
    });

    Route::group(['prefix' => 'unicenta'], function () {
        Route::apiResource('customers', CustomerController::class);
        Route::group(['prefix' => 'reports'], function () {
            Route::get('statement-of-account', [StatementOfAccountController::class, 'index']);
            Route::get('statement-of-account/{id}', [StatementOfAccountController::class, 'show']);
        });
    });
});
