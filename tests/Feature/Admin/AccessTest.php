<?php

use App\Models\User;

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
        '/admin/profile',
        '/admin/company',
        '/admin/clients',
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

test('the dashboard route sends admins to the admin panel', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->get('/dashboard')->assertRedirect('/admin');
});
