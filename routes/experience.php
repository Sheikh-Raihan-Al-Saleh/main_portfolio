<?php

use App\Http\Controllers\Experience\ExperienceController;
use Illuminate\Support\Facades\Route;

// Admin: the founder's work history.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(ExperienceController::class)->group(function () {
        Route::get('experiences', 'index')->name('experienceIndex');
        Route::get('experiences/create', 'create')->name('experienceCreate');
        Route::post('experiences', 'store')->name('experienceStore');
        Route::post('experiences/reorder', 'reorder')->name('experienceReorder');
        Route::get('experiences/{experience}/edit', 'edit')->name('experienceEdit');
        Route::put('experiences/{experience}', 'update')->name('experienceUpdate');
        Route::delete('experiences/{experience}', 'destroy')->name('experienceDestroy');
    });
});
