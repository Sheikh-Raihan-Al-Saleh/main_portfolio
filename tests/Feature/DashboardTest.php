<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

// /dashboard is now just the post-login landing spot; the real dashboard is
// the admin panel, so authenticated users are forwarded there.
test('authenticated users are forwarded to the admin dashboard', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('admin.dashboard'));
});
