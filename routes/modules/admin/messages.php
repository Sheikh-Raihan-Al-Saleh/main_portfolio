<?php

use App\Http\Controllers\Admin\MessageController;
use Illuminate\Support\Facades\Route;

// The contact-form inbox.
Route::controller(MessageController::class)
    ->prefix('messages')
    ->name('messages.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{message}', 'show')->name('show');
        Route::patch('{message}/unread', 'markUnread')->name('unread');
        Route::delete('{message}', 'destroy')->name('destroy');
    });
