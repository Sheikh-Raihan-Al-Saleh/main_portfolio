<?php

use App\Http\Controllers\Technology\TechnologyController;
use Illuminate\Support\Facades\Route;

// Admin: the tech-stack vocabulary.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(TechnologyController::class)->group(function () {
        Route::get('technologies', 'index')->name('technologyIndex');
        Route::post('technologies', 'store')->name('technologyStore');
        Route::put('technologies/{technology}', 'update')->name('technologyUpdate');
        Route::delete('technologies/{technology}', 'destroy')->name('technologyDestroy');
    });
});
