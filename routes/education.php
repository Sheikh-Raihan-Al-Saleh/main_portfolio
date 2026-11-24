<?php

use App\Http\Controllers\Education\EducationController;
use Illuminate\Support\Facades\Route;

// Admin: the founder's education history.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(EducationController::class)->group(function () {
        Route::get('educations', 'index')->name('educationIndex');
        Route::get('educations/create', 'create')->name('educationCreate');
        Route::post('educations', 'store')->name('educationStore');
        Route::post('educations/reorder', 'reorder')->name('educationReorder');
        Route::get('educations/{education}/edit', 'edit')->name('educationEdit');
        Route::put('educations/{education}', 'update')->name('educationUpdate');
        Route::delete('educations/{education}', 'destroy')->name('educationDestroy');
    });
});
