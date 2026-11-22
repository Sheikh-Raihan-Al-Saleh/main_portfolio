<?php

use App\Http\Controllers\Project\ProjectController;
use App\Http\Controllers\Project\ProjectPageController;
use Illuminate\Support\Facades\Route;

/*
| Project module: the work archive, project pages and the admin CRUD.
*/

// Public
Route::controller(ProjectPageController::class)
    ->prefix('projects')
    ->name('projects.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{project}', 'show')->name('show');
    });

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/projects')
    ->name('admin.projects.')
    ->controller(ProjectController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('{project}/edit', 'edit')->name('edit');
        Route::put('{project}', 'update')->name('update');
        Route::delete('{project}', 'destroy')->name('destroy');
    });
