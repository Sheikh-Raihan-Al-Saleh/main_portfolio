<?php

use App\Http\Controllers\Studio\StudioController;
use App\Http\Controllers\Studio\StudioPageController;
use Illuminate\Support\Facades\Route;

// Public: the company home page and about page.
Route::controller(StudioPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
});

// Admin: company details, brand and the home page copy.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(StudioController::class)->group(function () {
        Route::get('studio', 'edit')->name('studioEdit');
        Route::put('studio', 'update')->name('studioUpdate');
        Route::get('studio/content', 'editContent')->name('studioContentEdit');
        Route::put('studio/content', 'updateContent')->name('studioContentUpdate');
    });
});
