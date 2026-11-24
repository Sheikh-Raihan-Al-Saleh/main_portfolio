<?php

use App\Http\Controllers\Skill\SkillCategoryController;
use App\Http\Controllers\Skill\SkillController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    // Admin: the founder's skills.
    Route::controller(SkillController::class)->group(function () {
        Route::get('skills', 'index')->name('skillIndex');
        Route::get('skills/create', 'create')->name('skillCreate');
        Route::post('skills', 'store')->name('skillStore');
        Route::post('skills/reorder', 'reorder')->name('skillReorder');
        Route::get('skills/{skill}/edit', 'edit')->name('skillEdit');
        Route::put('skills/{skill}', 'update')->name('skillUpdate');
        Route::delete('skills/{skill}', 'destroy')->name('skillDestroy');
    });

    // Admin: the skill categories, managed on the skills page.
    Route::controller(SkillCategoryController::class)->group(function () {
        Route::post('skill-categories', 'store')->name('skillCategoryStore');
        Route::post('skill-categories/reorder', 'reorder')->name('skillCategoryReorder');
        Route::put('skill-categories/{skillCategory}', 'update')->name('skillCategoryUpdate');
        Route::delete('skill-categories/{skillCategory}', 'destroy')->name('skillCategoryDestroy');
    });
});
