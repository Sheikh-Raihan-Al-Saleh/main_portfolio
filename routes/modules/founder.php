<?php

use App\Http\Controllers\Founder\FounderController;
use App\Http\Controllers\Founder\FounderPageController;
use Illuminate\Support\Facades\Route;

/*
| Founder module: the personal portfolio and the founder profile editor.
*/

// Public
Route::controller(FounderPageController::class)->group(function () {
    Route::get('founder', 'show')->name('founder');
    Route::get('resume', 'resume')->name('resume');
});

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/founder')
    ->name('admin.founder.')
    ->controller(FounderController::class)
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });
