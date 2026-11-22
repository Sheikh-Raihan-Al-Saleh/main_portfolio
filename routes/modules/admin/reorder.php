<?php

use App\Http\Controllers\Admin\ReorderController;
use App\Services\ReorderService;
use Illuminate\Support\Facades\Route;

// Drag-and-drop ordering for every sortable list, e.g. POST /admin/reorder/projects.
Route::post('reorder/{type}', ReorderController::class)
    ->whereIn('type', array_keys(ReorderService::TYPES))
    ->name('reorder');
