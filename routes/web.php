<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DnsQueryController;

/*
|--------------------------------------------------------------------------
| Guest
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    });

    Route::post('/logout', LogoutController::class)
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');

    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
        ->name('dashboard.summary');

    /*
    |--------------------------------------------------------------------------
    | DNS Queries
    |--------------------------------------------------------------------------
    */

    Route::get('/dns-queries', [DnsQueryController::class, 'index'])
        ->name('dns-queries.index');

    Route::get('/dns-queries/data', [DnsQueryController::class, 'data'])
        ->name('dns-queries.data');

    Route::get('/dns-queries/filter-options', [DnsQueryController::class, 'filterOptions'])
        ->name('dns-queries.filter-options');

    Route::get('/dns-queries/{queryId}', [DnsQueryController::class, 'show'])
        ->where('queryId', '[a-f0-9]{64}')
        ->name('dns-queries.show');
});