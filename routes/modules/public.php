<?php

use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\FounderController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\LandingPageController;
use App\Http\Controllers\Public\ProjectController;
use App\Http\Controllers\Public\ResumeController;
use Illuminate\Support\Facades\Route;

/*
| The public website: the studio site, the founder's portfolio, project
| pages and the contact form.
*/

// Studio
Route::get('/', HomeController::class)->name('home');
Route::get('about', AboutController::class)->name('about');

// Founder
Route::get('founder', FounderController::class)->name('founder');
Route::get('resume', ResumeController::class)->name('resume');

// Work
Route::controller(ProjectController::class)
    ->prefix('projects')
    ->name('projects.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{project}', 'show')->name('show');
    });

// Short marketing URL for a project's case-study landing page.
Route::get('p/{project}', LandingPageController::class)->name('landing.show');

// Contact
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// Fortify sends users here after login; the admin panel is the real home.
Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/admin')->name('dashboard');
});
