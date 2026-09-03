<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'index'])->name('home');
Route::get('projects', [PortfolioController::class, 'projects'])->name('projects.index');
Route::get('projects/{project}', [PortfolioController::class, 'showProject'])->name('projects.show');
Route::get('resume', [PortfolioController::class, 'resume'])->name('resume');

// Serve uploaded media directly from storage, bypassing the symlink.
// This is required on shared hosting (CPanel) where `storage:link` is unreliable.
Route::get('media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

// Short marketing URL for a project's case-study landing page.
Route::get('p/{project}', [LandingPageController::class, 'show'])->name('landing.show');

Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// The starter kit's post-login destination; admins belong in the admin panel.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => redirect()->route('admin.dashboard'))->name('dashboard');
});

require __DIR__.'/admin.php';
require __DIR__.'/settings.php';
