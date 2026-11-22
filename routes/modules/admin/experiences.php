<?php

use App\Http\Controllers\Admin\ExperienceController;
use Illuminate\Support\Facades\Route;

Route::controller(ExperienceController::class)
    ->prefix('experiences')
    ->name('experiences.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{experience}/edit', 'edit')->name('edit');
        Route::put('{experience}', 'update')->name('update');
        Route::delete('{experience}', 'destroy')->name('destroy');
    });
