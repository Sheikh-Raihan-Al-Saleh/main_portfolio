<?php

use App\Http\Controllers\Admin\StudioController;
use Illuminate\Support\Facades\Route;

// The studio (company): brand, story, contact, home page blocks and copy.
Route::controller(StudioController::class)
    ->prefix('studio')
    ->name('studio.')
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::get('content', 'editContent')->name('content.edit');
        Route::put('content', 'updateContent')->name('content.update');
    });
