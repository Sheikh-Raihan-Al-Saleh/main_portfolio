<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

test('users are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});

test('the login screen showcases up to three published project covers', function () {
    Storage::fake('uploads');

    foreach (range(1, 4) as $i) {
        Storage::disk('uploads')->put("projects/cover-{$i}.png", 'x');
        Project::factory()->create(['cover_image_path' => "projects/cover-{$i}.png"]);
    }

    // Drafts and projects without a cover never reach the sign-in page.
    Project::factory()->draft()->create(['cover_image_path' => 'projects/cover-1.png']);
    Project::factory()->create(['cover_image_path' => null]);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('auth/Login')
            ->has('showcase', 3)
            ->has('showcase.0', fn ($item) => $item
                ->whereType('title', 'string')
                ->where('cover', fn (string $url) => str_contains($url, 'uploads/projects/cover-')),
            ),
        );
});

test('the login screen showcase is empty when no project has a cover', function () {
    Project::factory()->create(['cover_image_path' => null]);

    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('showcase', 0));
});
