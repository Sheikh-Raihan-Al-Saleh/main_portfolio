<?php

use App\Http\Controllers\Account\ProfileController;
use App\Http\Controllers\Account\SecurityController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Contact\MessageController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Education\EducationController;
use App\Http\Controllers\Experience\ExperienceController;
use App\Http\Controllers\Founder\FounderController;
use App\Http\Controllers\Founder\FounderPageController;
use App\Http\Controllers\LandingPage\LandingPageBuilderController;
use App\Http\Controllers\LandingPage\LandingPageController;
use App\Http\Controllers\LandingPage\LandingSectionController;
use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectPageController;
use App\Http\Controllers\Skill\SkillCategoryController;
use App\Http\Controllers\Skill\SkillController;
use App\Http\Controllers\Studio\StudioController;
use App\Http\Controllers\Studio\StudioPageController;
use App\Http\Controllers\Technology\TechnologyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| Every route of the application, grouped by module. Public pages come
| first, then the admin panel, then the signed-in user's own settings.
|
| CRUD modules use Route::resource, so each route maps to the matching
| resource controller method (index, create, store, edit, update, destroy).
| Pages that edit a single record (studio, founder, a landing page) have
| plain edit/update routes instead.
|
| Login, password reset and two-factor routes are registered by Fortify.
|
*/

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

// Studio module: the company home page and about page.
Route::controller(StudioPageController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('about', 'about')->name('about');
});

// Founder module: the personal portfolio and the resume download.
Route::controller(FounderPageController::class)->group(function () {
    Route::get('founder', 'show')->name('founder');
    Route::get('resume', 'resume')->name('resume');
});

// Project module: the work archive and the project detail pages.
Route::resource('projects', ProjectPageController::class)->only(['index', 'show']);

// LandingPage module: a project's case-study page.
Route::get('p/{project}', [LandingPageController::class, 'show'])->name('landing.show');

// Contact module: the public contact form (5 messages a minute at most).
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Fortify sends users to /dashboard after login; the admin panel is home.
    Route::redirect('dashboard', '/admin')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Dashboard module: the admin home.
        Route::get('/', DashboardController::class)->name('dashboard');

        // Studio module: company details, brand and the home page copy.
        Route::controller(StudioController::class)
            ->prefix('studio')
            ->name('studio.')
            ->group(function () {
                Route::get('/', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::get('content', 'editContent')->name('content.edit');
                Route::put('content', 'updateContent')->name('content.update');
            });

        // Founder module: the founder profile editor.
        Route::controller(FounderController::class)
            ->prefix('founder')
            ->name('founder.')
            ->group(function () {
                Route::get('/', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
            });

        // Project module: the projects CRUD.
        Route::post('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
        Route::resource('projects', ProjectController::class)->except(['show']);

        // LandingPage module: the builder of one project's landing page.
        Route::controller(LandingPageBuilderController::class)
            ->prefix('projects/{project}/landing')
            ->name('projects.landing.')
            ->group(function () {
                Route::get('/', 'edit')->name('edit');
                Route::put('/', 'update')->name('update');
                Route::delete('/', 'destroy')->name('destroy');
            });

        // LandingPage module: content blocks of the home and landing pages.
        Route::controller(LandingSectionController::class)
            ->prefix('landing-sections')
            ->name('landing-sections.')
            ->group(function () {
                Route::post('reorder', 'reorder')->name('reorder');
                Route::post('media', 'uploadMedia')->name('media.store');
            });
        Route::resource('landing-sections', LandingSectionController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['landing-sections' => 'landingSection']);

        // Technology module: the tech-stack vocabulary.
        Route::resource('technologies', TechnologyController::class)->only(['index', 'store', 'update', 'destroy']);

        // Client module: the studio's clients and partners.
        Route::post('clients/reorder', [ClientController::class, 'reorder'])->name('clients.reorder');
        Route::resource('clients', ClientController::class)->only(['index', 'store', 'update', 'destroy']);

        // Skill module: the founder's skills.
        Route::post('skills/reorder', [SkillController::class, 'reorder'])->name('skills.reorder');
        Route::resource('skills', SkillController::class)->except(['show']);

        // Skill module: the skill categories.
        Route::post('skill-categories/reorder', [SkillCategoryController::class, 'reorder'])->name('skill-categories.reorder');
        Route::resource('skill-categories', SkillCategoryController::class)
            ->only(['store', 'update', 'destroy'])
            ->parameters(['skill-categories' => 'skillCategory']);

        // Experience module: the founder's work history.
        Route::post('experiences/reorder', [ExperienceController::class, 'reorder'])->name('experiences.reorder');
        Route::resource('experiences', ExperienceController::class)->except(['show']);

        // Education module: the founder's education history.
        Route::post('educations/reorder', [EducationController::class, 'reorder'])->name('educations.reorder');
        Route::resource('educations', EducationController::class)->except(['show']);

        // Contact module: the inbox. Opening a message marks it read.
        Route::patch('messages/{message}/unread', [MessageController::class, 'markUnread'])->name('messages.unread');
        Route::resource('messages', MessageController::class)->only(['index', 'show', 'destroy']);
    });

/*
|--------------------------------------------------------------------------
| Account settings
|--------------------------------------------------------------------------
*/

// Account module: the signed-in user's own settings.
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
