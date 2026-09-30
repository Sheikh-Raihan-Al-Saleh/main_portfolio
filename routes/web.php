<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PortfolioController;
use Illuminate\Support\Facades\Route;

// The company site: sheikhnabil.com.
Route::get('/', [CompanyController::class, 'index'])->name('home');

Route::get('about', [CompanyController::class, 'about'])->name('about');
Route::get('projects', [PortfolioController::class, 'projects'])->name('projects.index');
Route::get('projects/{project}', [PortfolioController::class, 'showProject'])->name('projects.show');

// The founder's own portfolio. Reached from the founder card on the company
// About page, so it is a destination rather than part of the company site.
Route::get('founder', [PortfolioController::class, 'founder'])->name('founder');
Route::get('resume', [PortfolioController::class, 'resume'])->name('resume');

// Legacy media URLs. New code links directly to static files under /uploads/;
// this route keeps previously shared or cached /media/ links working (it
// falls back to the pre-migration storage/app/public disk as well).
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
