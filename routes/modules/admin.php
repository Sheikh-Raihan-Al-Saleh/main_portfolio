<?php

use Illuminate\Support\Facades\Route;

/*
| The admin panel. Every route is under /admin, named admin.*, and limited to
| signed-in, verified admins. Each module's routes live in routes/modules/admin.
*/

Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        require __DIR__.'/admin/dashboard.php';
        require __DIR__.'/admin/studio.php';
        require __DIR__.'/admin/founder.php';
        require __DIR__.'/admin/projects.php';
        require __DIR__.'/admin/landing.php';
        require __DIR__.'/admin/technologies.php';
        require __DIR__.'/admin/clients.php';
        require __DIR__.'/admin/skills.php';
        require __DIR__.'/admin/experiences.php';
        require __DIR__.'/admin/educations.php';
        require __DIR__.'/admin/messages.php';
        require __DIR__.'/admin/reorder.php';
    });
