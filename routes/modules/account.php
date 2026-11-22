<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use Illuminate\Support\Facades\Route;

/*
| Account module: the signed-in user's own settings.
*/

Route::middleware('auth')->prefix('settings')->group(function () {
    Route::redirect('/', '/settings/profile');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('profile', 'edit')->name('profile.edit');
        Route::patch('profile', 'update')->name('profile.update');
        Route::delete('profile', 'destroy')->middleware('verified')->name('profile.destroy');
    });

    Route::middleware('verified')->group(function () {
        Route::controller(SecurityController::class)->group(function () {
            Route::get('security', 'edit')->name('security.edit');
            Route::put('password', 'update')->middleware('throttle:6,1')->name('user-password.update');
        });

        Route::inertia('appearance', 'settings/Appearance')->name('appearance.edit');
    });
});
