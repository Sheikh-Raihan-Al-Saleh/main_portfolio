<?php

use App\Http\Controllers\Client\ClientController;
use Illuminate\Support\Facades\Route;

// Admin: the studio's clients and partners.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::controller(ClientController::class)->group(function () {
        Route::get('clients', 'index')->name('clientIndex');
        Route::post('clients', 'store')->name('clientStore');
        Route::post('clients/reorder', 'reorder')->name('clientReorder');
        Route::put('clients/{client}', 'update')->name('clientUpdate');
        Route::delete('clients/{client}', 'destroy')->name('clientDestroy');
    });
});
