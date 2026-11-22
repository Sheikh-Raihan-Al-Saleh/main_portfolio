<?php

use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Contact\MessageController;
use Illuminate\Support\Facades\Route;

/*
| Contact module: the public contact form and the admin inbox.
*/

// Public
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

// Admin
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin/messages')
    ->name('admin.messages.')
    ->controller(MessageController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('{message}', 'show')->name('show');
        Route::patch('{message}/unread', 'markUnread')->name('unread');
        Route::delete('{message}', 'destroy')->name('destroy');
    });
