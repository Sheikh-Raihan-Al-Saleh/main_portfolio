<?php

use App\Http\Controllers\Studio\StudioController;
use App\Http\Controllers\Studio\StudioPageController;
use Illuminate\Support\Facades\Route;

/*
| Studio module: the company's public pages and its admin settings.
*/

// Public
Route::controller(StudioPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
});

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/studio')
    ->name('admin.studio.')
    ->controller(StudioController::class)
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::get('content', 'editContent')->name('content.edit');
        Route::put('content', 'updateContent')->name('content.update');
    });
