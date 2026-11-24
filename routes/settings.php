<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use Illuminate\Support\Facades\Route;

// Account: the signed-in user's own settings.
Route::middleware('auth')->prefix('settings')->group(function () {
    Route::redirect('/', '/settings/profile');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'edit')->name('profileEdit');
        Route::patch('profile', 'update')->name('profileUpdate');
        Route::delete('profile', 'destroy')->middleware('verified')->name('profileDestroy');
    });

    Route::middleware('verified')->group(function () {
        Route::controller(SecurityController::class)->group(function () {
            Route::get('security', 'edit')->name('securityEdit');
            Route::put('password', 'update')->middleware('throttle:6,1')->name('passwordUpdate');
        });

        Route::inertia('appearance', 'settings/Appearance')->name('appearanceEdit');
    });
});
