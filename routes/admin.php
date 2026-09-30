<?php

use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EducationController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LandingSectionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SiteProfileController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StudioContentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::post('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
        Route::post('skills/reorder', [SkillController::class, 'reorder'])->name('skills.reorder');
        Route::post('experiences/reorder', [ExperienceController::class, 'reorder'])->name('experiences.reorder');
        Route::post('educations/reorder', [EducationController::class, 'reorder'])->name('educations.reorder');
        Route::post('clients/reorder', [ClientController::class, 'reorder'])->name('clients.reorder');

        // Declared before the projects resource so `projects/{project}/landing`
        // is not swallowed by the resource's own wildcard routes.
        Route::get('projects/{project}/landing', [LandingPageController::class, 'edit'])
            ->name('projects.landing.edit');
        Route::post('projects/{project}/landing', [LandingPageController::class, 'update'])
            ->name('projects.landing.update');

        Route::delete('projects/{project}/landing', [LandingPageController::class, 'destroy'])
            ->name('projects.landing.destroy');

        // Section payloads store media as plain paths, so uploads happen up
        // front and the returned path is what gets saved into `data`.
        Route::post('landing-media', [LandingSectionController::class, 'uploadMedia'])
            ->name('landing-media.store');

        Route::post('landing-sections/reorder', [LandingSectionController::class, 'reorder'])
            ->name('landing-sections.reorder');
        Route::resource('landing-sections', LandingSectionController::class)
            ->only(['store', 'update', 'destroy']);

        Route::resource('projects', ProjectController::class)->except('show');
        Route::resource('skills', SkillController::class)->except('show');
        Route::resource('experiences', ExperienceController::class)->except('show');
        Route::resource('educations', EducationController::class)->except('show');
        Route::resource('clients', ClientController::class)->only(['store', 'update', 'destroy']);

        // The studio home page's fixed-section copy.
        Route::get('studio-sections', [StudioContentController::class, 'edit'])
            ->name('studio-sections.edit');
        Route::post('studio-sections', [StudioContentController::class, 'update'])
            ->name('studio-sections.update');

        Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
        Route::patch('messages/{message}/unread', [ContactMessageController::class, 'markUnread'])->name('messages.unread');
        Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        // The company home page and the founder behind it are separate records,
        // so each gets its own editor.
        Route::get('company', [CompanyController::class, 'edit'])->name('company.edit');
        Route::post('company', [CompanyController::class, 'update'])->name('company.update');

        Route::get('clients', [ClientController::class, 'index'])->name('clients.index');

        Route::get('profile', [SiteProfileController::class, 'edit'])->name('profile.edit');
        Route::post('profile', [SiteProfileController::class, 'update'])->name('profile.update');
    });
