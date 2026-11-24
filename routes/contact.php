<?php

use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Contact\MessageController;
use Illuminate\Support\Facades\Route;

// Public: the contact form (5 messages a minute at most).
Route::post('contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contactStore');

// Admin: the inbox. Opening a message marks it read.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(MessageController::class)->group(function () {
        Route::get('messages', 'index')->name('messageIndex');
        Route::get('messages/{message}', 'show')->name('messageShow');
        Route::patch('messages/{message}/unread', 'markUnread')->name('messageUnread');
        Route::delete('messages/{message}', 'destroy')->name('messageDestroy');
    });
});
