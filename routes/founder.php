<?php

use App\Http\Controllers\Founder\FounderController;
use App\Http\Controllers\Founder\FounderPageController;
use Illuminate\Support\Facades\Route;

// Public: the personal portfolio and the resume download.
Route::controller(FounderPageController::class)->group(function () {
    Route::get('founder', 'show')->name('founder');
    Route::get('resume', 'resume')->name('resume');
});

// Admin: the founder profile editor.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(FounderController::class)->group(function () {
        Route::get('founder', 'edit')->name('founderEdit');
        Route::put('founder', 'update')->name('founderUpdate');
    });
});
