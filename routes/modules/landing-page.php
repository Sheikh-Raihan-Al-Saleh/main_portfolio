<?php

use App\Http\Controllers\LandingPage\LandingPageBuilderController;
use App\Http\Controllers\LandingPage\LandingPageController;
use App\Http\Controllers\LandingPage\LandingSectionController;
use Illuminate\Support\Facades\Route;

/*
| LandingPage module: case-study pages (/p/{slug}), the page builder and the
| content blocks shared with the studio home page.
*/

// Public
Route::get('p/{project}', [LandingPageController::class, 'show'])->name('landing.show');

// Admin: the builder of one project's landing page
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/projects/{project}/landing')
    ->name('admin.projects.landing.')
    ->controller(LandingPageBuilderController::class)
    ->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::delete('/', 'destroy')->name('destroy');
    });

// Admin: content blocks
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/landing-sections')
    ->name('admin.landing-sections.')
    ->controller(LandingSectionController::class)
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::post('media', 'uploadMedia')->name('media.store');
        Route::put('{landingSection}', 'update')->name('update');
        Route::delete('{landingSection}', 'destroy')->name('destroy');
    });
