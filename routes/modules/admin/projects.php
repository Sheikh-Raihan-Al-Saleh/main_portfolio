<?php

use App\Http\Controllers\Admin\ProjectController;
use Illuminate\Support\Facades\Route;

Route::controller(ProjectController::class)
    ->prefix('projects')
    ->name('projects.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{project}/edit', 'edit')->name('edit');
        Route::put('{project}', 'update')->name('update');
        Route::delete('{project}', 'destroy')->name('destroy');
    });
