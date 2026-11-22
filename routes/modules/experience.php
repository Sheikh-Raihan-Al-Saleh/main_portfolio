<?php

use App\Http\Controllers\Experience\ExperienceController;
use Illuminate\Support\Facades\Route;

/*
| Experience module: the founder's work history (admin only).
*/

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/experiences')
    ->name('admin.experiences.')
    ->controller(ExperienceController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('{experience}/edit', 'edit')->name('edit');
        Route::put('{experience}', 'update')->name('update');
        Route::delete('{experience}', 'destroy')->name('destroy');
    });
