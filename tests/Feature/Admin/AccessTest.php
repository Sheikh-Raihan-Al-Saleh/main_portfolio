<?php

use App\Models\User;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Route;

/**
 * Every admin route reachable with a plain GET, used to assert the guard
 * applies uniformly rather than route by route.
 *
 * @return array<int, string>
 */
function adminGetRoutes(): array
{
    return [
        '/admin',
        '/admin/projects',
        '/admin/projects/create',
        '/admin/skills',
        '/admin/skills/create',
        '/admin/experiences',
        '/admin/experiences/create',
        '/admin/educations',
        '/admin/educations/create',
        '/admin/messages',
        '/admin/studio',
        '/admin/studio/content',
        '/admin/founder',
        '/admin/clients',
        '/admin/technologies',
    ];
}

test('guests are redirected to login from every admin route', function (string $uri) {
    $this->get($uri)->assertRedirect('/login');
})->with(adminGetRoutes());

test('authenticated non-admins are forbidden from every admin route', function (string $uri) {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)->get($uri)->assertForbidden();
})->with(adminGetRoutes());

test('admins can reach every admin route', function (string $uri) {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get($uri)->assertOk();
})->with(adminGetRoutes());

test('every admin route, of every method, runs the admin middleware', function () {
    // Admin controllers register "admin" in their constructor (Ahad style),
    // so this catches a controller that forgets it, write routes included.
    $unguarded = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (IlluminateRoute $route): bool => $route->uri() === 'admin' || str_starts_with($route->uri(), 'admin/'))
        ->reject(fn (IlluminateRoute $route): bool => in_array('admin', $route->gatherMiddleware(), true))
        ->map(fn (IlluminateRoute $route): string => implode('|', $route->methods()).' '.$route->uri())
        ->values()
        ->all();

    expect($unguarded)->toBe([]);
});

test('the dashboard route sends admins to the admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');
});
