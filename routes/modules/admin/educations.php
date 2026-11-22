<?php

use App\Http\Controllers\Admin\EducationController;
use Illuminate\Support\Facades\Route;

Route::controller(EducationController::class)
    ->prefix('educations')
    ->name('educations.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{education}/edit', 'edit')->name('edit');
        Route::put('{education}', 'update')->name('update');
        Route::delete('{education}', 'destroy')->name('destroy');
    });
