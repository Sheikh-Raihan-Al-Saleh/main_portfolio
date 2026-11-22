<?php

use App\Http\Controllers\Admin\SkillCategoryController;
use App\Http\Controllers\Admin\SkillController;
use Illuminate\Support\Facades\Route;

Route::controller(SkillController::class)
    ->prefix('skills')
    ->name('skills.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('{skill}/edit', 'edit')->name('edit');
        Route::put('{skill}', 'update')->name('update');
        Route::delete('{skill}', 'destroy')->name('destroy');
    });

Route::controller(SkillCategoryController::class)
    ->prefix('skill-categories')
    ->name('skill-categories.')
    ->group(function () {
        Route::post('/', 'store')->name('store');
        Route::put('{skillCategory}', 'update')->name('update');
        Route::delete('{skillCategory}', 'destroy')->name('destroy');
    });
