<?php

use App\Models\User;

test('the web manifest describes the installable admin app', function () {
    $response = $this->get(route('pwaManifest'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/manifest+json')
        ->assertJsonPath('start_url', '/admin')
        ->assertJsonPath('scope', '/')
        ->assertJsonPath('display', 'standalone')
        ->assertJsonCount(3, 'icons');

    foreach ($response->json('icons') as $icon) {
        expect(public_path(ltrim($icon['src'], '/')))->toBeFile();
    }
});

test('the service worker is served uncached with its version filled in', function () {
    $response = $this->get(route('pwaServiceWorker'));

    $response->assertOk()
        ->assertHeader('Service-Worker-Allowed', '/')
        ->assertHeader('Content-Type', 'application/javascript; charset=utf-8');

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->getContent())
        ->not->toContain('__VERSION__')
        ->toMatch("/const VERSION = '[a-f0-9]{12}';/");
});

test('admin pages link the manifest', function () {
    $this->actingAs(User::factory()->create(['is_admin' => true]));

    $this->get(route('adminDashboard'))
        ->assertOk()
        ->assertSee('rel="manifest"', false);
});

test('public pages do not offer the admin app', function () {
    $this->get('/')
        ->assertOk()
        ->assertDontSee('rel="manifest"', false);
});
