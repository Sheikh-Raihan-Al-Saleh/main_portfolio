<?php

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| One route file per module, each loaded below. Route names are camelCase
| and verb style (projectIndex, projectStore, ...). Every module file holds
| the module's public pages first, then its admin pages under /admin.
|
| Admin pages live under /admin and use the "auth", "verified" and "admin"
| middleware, set in each route file.
|
| Login, password reset and two-factor routes are registered by Fortify.
|
*/

require __DIR__.'/pwa.php';
require __DIR__.'/studio.php';
require __DIR__.'/founder.php';
require __DIR__.'/project.php';
require __DIR__.'/landingPage.php';
require __DIR__.'/contact.php';
require __DIR__.'/dashboard.php';
require __DIR__.'/technology.php';
require __DIR__.'/client.php';
require __DIR__.'/skill.php';
require __DIR__.'/experience.php';
require __DIR__.'/education.php';
require __DIR__.'/settings.php';
