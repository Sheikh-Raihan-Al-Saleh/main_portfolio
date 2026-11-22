<?php

/*
|--------------------------------------------------------------------------
| Web routes
|--------------------------------------------------------------------------
|
| Every module keeps its routes (public pages and admin panel) in its own
| file under routes/modules. This file only loads them.
|
| Login, password reset and two-factor routes are registered by Fortify.
|
*/

require __DIR__.'/modules/studio.php';
require __DIR__.'/modules/founder.php';
require __DIR__.'/modules/project.php';
require __DIR__.'/modules/landing-page.php';
require __DIR__.'/modules/technology.php';
require __DIR__.'/modules/client.php';
require __DIR__.'/modules/skill.php';
require __DIR__.'/modules/experience.php';
require __DIR__.'/modules/education.php';
require __DIR__.'/modules/contact.php';
require __DIR__.'/modules/dashboard.php';
require __DIR__.'/modules/account.php';
