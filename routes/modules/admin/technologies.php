<?php

use App\Http\Controllers\Admin\TechnologyController;
use Illuminate\Support\Facades\Route;

Route::controller(TechnologyController::class)
    ->prefix('technologies')
    ->name('technologies.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('{technology}', 'update')->name('update');
        Route::delete('{technology}', 'destroy')->name('destroy');
    });
