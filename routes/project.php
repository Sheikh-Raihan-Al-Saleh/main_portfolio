<?php

use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectPageController;
use Illuminate\Support\Facades\Route;

// Public: the work archive and the project detail pages.
Route::controller(ProjectPageController::class)->group(function () {
    Route::get('projects', 'index')->name('projectArchive');
    Route::get('projects/{project}', 'show')->name('projectShow');
});

// Admin: the projects CRUD.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(ProjectController::class)->group(function () {
        Route::get('projects', 'index')->name('projectIndex');
        Route::get('projects/create', 'create')->name('projectCreate');
        Route::post('projects', 'store')->name('projectStore');
        Route::post('projects/reorder', 'reorder')->name('projectReorder');
        Route::get('projects/{project}/edit', 'edit')->name('projectEdit');
        Route::put('projects/{project}', 'update')->name('projectUpdate');
        Route::delete('projects/{project}', 'destroy')->name('projectDestroy');
    });
});
