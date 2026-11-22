<?php

use App\Http\Controllers\Admin\FounderController;
use Illuminate\Support\Facades\Route;

// The founder behind the /founder portfolio.
Route::controller(FounderController::class)
    ->prefix('founder')
    ->name('founder.')
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });
