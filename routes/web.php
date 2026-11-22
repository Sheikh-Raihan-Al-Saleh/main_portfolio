<?php

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| The single entry point for every browser route. Each module keeps its
| routes in its own file under routes/modules; this file only wires them in.
|
|   public.php   the studio site, the founder portfolio, contact form
|   admin.php    the admin panel (requires routes/modules/admin/*.php)
|   settings.php the signed-in admin's own account
|
| Authentication routes (login, password reset, two-factor) are registered
| by Laravel Fortify.
|
*/

require __DIR__.'/modules/public.php';
require __DIR__.'/modules/admin.php';
require __DIR__.'/modules/settings.php';
