<?php

use App\Http\Controllers\Admin\ClientController;
use Illuminate\Support\Facades\Route;

Route::controller(ClientController::class)
    ->prefix('clients')
    ->name('clients.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('{client}', 'update')->name('update');
        Route::delete('{client}', 'destroy')->name('destroy');
    });
