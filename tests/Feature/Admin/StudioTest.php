<?php

use App\Models\User;
use App\Services\StudioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->admin = admin();
});

test('a non-admin cannot edit the studio', function () {
    $this->actingAs(User::factory()->create())
        ->put('/admin/studio', ['name' => 'Hacked'])
        ->assertForbidden();
});

test('the studio form loads with its social links and home blocks', function () {
    $this->actingAs($this->admin)
        ->get('/admin/studio')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/studio/Edit')
            ->has('studio')
            ->has('socials')
            ->has('sections')
            ->has('sectionTypes'),
        );
});

test('an admin can rename the studio and upload its logos', function () {
    $this->actingAs($this->admin)
        ->put('/admin/studio', [
            'name' => 'Acme Studio',
            'short_name' => 'Acme',
            'logo' => UploadedFile::fake()->image('logo.png', 600, 160),
            'logo_mark' => UploadedFile::fake()->image('mark.png', 256, 256),
            'socials' => ['github' => 'https://github.com/acme', 'linkedin' => ''],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $studio = app(StudioService::class)->current()->fresh('socialLinks');

    expect($studio->name)->toBe('Acme Studio')
        ->and($studio->slug)->toBe('acme-studio')
        ->and($studio->socialLinks->pluck('platform')->all())->toBe(['github']);

    Storage::disk('uploads')->assertExists([$studio->logo_path, $studio->logo_mark_path]);
});

test('the brand updates everywhere as soon as the studio is saved', function () {
    $this->actingAs($this->admin)->get('/')->assertInertia(fn ($page) => $page->where('brand.name', 'Your Studio'));

    $this->actingAs($this->admin)->put('/admin/studio', [
        'name' => 'Renamed Studio',
        'logo' => UploadedFile::fake()->image('logo.png', 600, 160),
    ]);

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('brand.name', 'Renamed Studio')
        ->where('brand.logo_url', fn (string $url) => str_starts_with($url, '/uploads/brand/')),
    );
});

test('the logo mark must be square', function () {
    $this->actingAs($this->admin)
        ->put('/admin/studio', [
            'name' => 'Acme',
            'logo_mark' => UploadedFile::fake()->image('mark.png', 400, 100),
        ])
        ->assertSessionHasErrors('logo_mark');
});

test('replacing a logo deletes the old file and remove clears it', function () {
    $this->actingAs($this->admin)->put('/admin/studio', [
        'name' => 'Acme',
        'logo' => UploadedFile::fake()->image('one.png', 600, 160),
    ]);
    $first = app(StudioService::class)->current()->fresh()->logo_path;

    $this->actingAs($this->admin)->put('/admin/studio', [
        'name' => 'Acme',
        'logo' => UploadedFile::fake()->image('two.png', 600, 160),
    ]);
    Storage::disk('uploads')->assertMissing($first);

    $this->actingAs($this->admin)->put('/admin/studio', ['name' => 'Acme', 'remove_logo' => true]);
    expect(app(StudioService::class)->current()->fresh()->logo_path)->toBeNull();
});

test('the home page copy can be edited and merges over the defaults', function () {
    $this->actingAs($this->admin)
        ->get('/admin/studio/content')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/studio/Content')->has('content.cta'));

    $this->actingAs($this->admin)
        ->put('/admin/studio/content', ['content' => ['cta' => ['title' => 'Custom CTA']]])
        ->assertRedirect();

    $this->get('/')->assertInertia(fn ($page) => $page
        ->where('content.cta.title', 'Custom CTA')
        ->has('content.cta.button_label'),
    );
});
