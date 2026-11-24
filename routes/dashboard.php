<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    // Fortify sends users to /dashboard after login; the admin panel is home.
    Route::redirect('dashboard', '/admin')->name('dashboard');

    // Admin: the dashboard home page.
    Route::get('admin', [DashboardController::class, 'index'])->middleware('admin')->name('adminDashboard');
});
