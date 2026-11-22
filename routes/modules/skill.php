<?php

use App\Http\Controllers\Skill\SkillCategoryController;
use App\Http\Controllers\Skill\SkillController;
use Illuminate\Support\Facades\Route;

/*
| Skill module: the founder's skills and skill categories (admin only).
*/

// Admin: skills
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/skills')
    ->name('admin.skills.')
    ->controller(SkillController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::get('{skill}/edit', 'edit')->name('edit');
        Route::put('{skill}', 'update')->name('update');
        Route::delete('{skill}', 'destroy')->name('destroy');
    });

// Admin: skill categories
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/skill-categories')
    ->name('admin.skill-categories.')
    ->controller(SkillCategoryController::class)
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::post('reorder', 'reorder')->name('reorder');
        Route::put('{skillCategory}', 'update')->name('update');
        Route::delete('{skillCategory}', 'destroy')->name('destroy');
    });
