<?php

use App\Http\Controllers\Technology\TechnologyController;
use Illuminate\Support\Facades\Route;

/*
| Technology module: the tech-stack vocabulary (admin only).
*/

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/technologies')
    ->name('admin.technologies.')
    ->controller(TechnologyController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('{technology}', 'update')->name('update');
        Route::delete('{technology}', 'destroy')->name('destroy');
    });
