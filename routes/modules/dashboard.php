<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

/*
| Dashboard module: the admin home.
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Fortify sends users to /dashboard after login; the admin panel is home.
    Route::redirect('dashboard', '/admin')->name('dashboard');

    Route::get('admin', DashboardController::class)->middleware('admin')->name('admin.dashboard');
});
