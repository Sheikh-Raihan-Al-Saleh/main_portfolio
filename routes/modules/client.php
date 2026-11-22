<?php

use App\Http\Controllers\Client\ClientController;
use Illuminate\Support\Facades\Route;

/*
| Client module: the studio's clients and partners (admin only).
*/

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/clients')
    ->name('admin.clients.')
    ->controller(ClientController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::put('{client}', 'update')->name('update');
        Route::delete('{client}', 'destroy')->name('destroy');
    });
