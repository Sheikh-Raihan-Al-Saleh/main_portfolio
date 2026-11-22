<?php

use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LandingSectionController;
use Illuminate\Support\Facades\Route;

// A project's case-study landing page builder.
Route::controller(LandingPageController::class)
    ->prefix('projects/{project}/landing')
    ->name('projects.landing.')
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

// Content blocks, shared by the studio home page and landing pages.
Route::controller(LandingSectionController::class)
    ->prefix('landing-sections')
    ->name('landing-sections.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        // Block media is uploaded first; the returned path is saved into the block.
        Route::post('media', 'uploadMedia')->name('media.store');
        Route::put('{landingSection}', 'update')->name('update');
        Route::delete('{landingSection}', 'destroy')->name('destroy');
    });
