<?php

use App\Http\Controllers\LandingPage\LandingPageBuilderController;
use App\Http\Controllers\LandingPage\LandingPageController;
use App\Http\Controllers\LandingPage\LandingSectionController;
use Illuminate\Support\Facades\Route;

// Public: a project's case-study page.
Route::get('p/{project}', [LandingPageController::class, 'show'])->name('landingPageShow');

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    // Admin: the builder of one project's landing page.
    Route::controller(LandingPageBuilderController::class)->group(function () {
        Route::get('projects/{project}/landing', 'edit')->name('landingPageEdit');
        Route::put('projects/{project}/landing', 'update')->name('landingPageUpdate');
        Route::delete('projects/{project}/landing', 'destroy')->name('landingPageDestroy');
    });

    // Admin: content blocks of the home and landing pages.
    Route::controller(LandingSectionController::class)->group(function () {
        Route::post('landing-sections', 'store')->name('landingSectionStore');
        Route::post('landing-sections/reorder', 'reorder')->name('landingSectionReorder');
        Route::post('landing-sections/media', 'uploadMedia')->name('landingSectionMediaStore');
        Route::put('landing-sections/{landingSection}', 'update')->name('landingSectionUpdate');
        Route::delete('landing-sections/{landingSection}', 'destroy')->name('landingSectionDestroy');
    });
});
