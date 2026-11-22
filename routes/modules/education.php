<?php

use App\Http\Controllers\Education\EducationController;
use Illuminate\Support\Facades\Route;

/*
| Education module: the founder's education history (admin only).
*/

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/educations')
    ->name('admin.educations.')
    ->controller(EducationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('{education}/edit', 'edit')->name('edit');
        Route::put('{education}', 'update')->name('update');
        Route::delete('{education}', 'destroy')->name('destroy');
    });
