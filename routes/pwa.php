<?php

use App\Http\Controllers\Pwa\PwaController;
use Illuminate\Support\Facades\Route;

// Public: the admin panel's installable-app files (see PwaService).
Route::get('sw.js', [PwaController::class, 'serviceWorker'])->name('pwaServiceWorker');
Route::get('admin/manifest.webmanifest', [PwaController::class, 'manifest'])->name('pwaManifest');
