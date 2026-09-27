<?php

use App\Models\Company;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('a non-admin cannot change the website logo', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post('/admin/company', [
            'name' => 'Sheikh Nabil',
            'logo' => UploadedFile::fake()->image('brand.png'),
        ])
        ->assertForbidden();
});

test('an admin can upload the website logo and it renders site-wide', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)
        ->post('/admin/company', [
            'name' => 'Sheikh Nabil',
            'logo' => UploadedFile::fake()->image('brand.png'),
        ])
        ->assertRedirect();

    $company = Company::current()->fresh();

    expect($company->logo_path)->not->toBeNull();
    Storage::disk('uploads')->assertExists($company->logo_path);

    // The uploaded logo reaches the public header and footer on both faces
    // of the site, with no code change in between.
    foreach (['/', '/about', '/founder'] as $path) {
        expect($this->get($path)->assertOk()->getContent())
            ->toContain('uploads/'.$company->logo_path);
    }
});

test('an admin can remove the website logo and the initials return', function () {
    Storage::fake('uploads');
    $path = UploadedFile::fake()->image('brand.png')->store('company', 'uploads');
    Company::current()->update(['logo_path' => $path]);

    $this->actingAs($this->admin)
        ->post('/admin/company', [
            'name' => 'Sheikh Nabil',
            'remove_logo' => 1,
        ])
        ->assertRedirect();

    expect(Company::current()->fresh()->logo_path)->toBeNull();
    Storage::disk('uploads')->assertMissing($path);
    expect($this->get('/')->assertOk()->getContent())->toContain('footer-logo-icon');
});

test('a non-admin cannot change the website logo from the profile section', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post('/admin/profile', [
            'name' => 'Ada Lovelace',
            'logo' => UploadedFile::fake()->image('brand.png'),
        ])
        ->assertForbidden();
});

test('an admin can upload the website logo from the profile section', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)
        ->post('/admin/profile', [
            'name' => 'Ada Lovelace',
            'logo' => UploadedFile::fake()->image('brand.png'),
        ])
        ->assertRedirect();

    $profile = Profile::current()->fresh();

    expect($profile->logo_path)->not->toBeNull();
    Storage::disk('uploads')->assertExists($profile->logo_path);

    // The profile logo reaches the header and footer on both faces of the site.
    foreach (['/', '/founder'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)
            ->toContain('uploads/'.$profile->logo_path)
            ->not->toContain('footer-logo-icon');
    }
});

test('the profile logo wins over the older company logo', function () {
    Storage::fake('uploads');
    $companyPath = UploadedFile::fake()->image('old.png')->store('company', 'uploads');
    $profilePath = UploadedFile::fake()->image('new.png')->store('profile', 'uploads');
    Company::current()->update(['logo_path' => $companyPath]);
    Profile::current()->update(['logo_path' => $profilePath]);

    $html = $this->get('/')->assertOk()->getContent();
    $header = Str::between($html, '<header', '</header>');
    $footer = Str::between($html, '<footer', '</footer>');

    // The brand mark prefers the profile logo; the older company file may
    // still appear in page content such as the hero.
    expect($header)->toContain('uploads/'.$profilePath)
        ->and($header)->not->toContain('uploads/'.$companyPath)
        ->and($footer)->toContain('uploads/'.$profilePath)
        ->and($footer)->not->toContain('uploads/'.$companyPath);
});

test('removing the profile logo falls back to the company logo', function () {
    Storage::fake('uploads');
    $companyPath = UploadedFile::fake()->image('old.png')->store('company', 'uploads');
    $profilePath = UploadedFile::fake()->image('new.png')->store('profile', 'uploads');
    Company::current()->update(['logo_path' => $companyPath]);
    Profile::current()->update(['logo_path' => $profilePath]);

    $this->actingAs($this->admin)
        ->post('/admin/profile', [
            'name' => 'Ada Lovelace',
            'remove_logo' => 1,
        ])
        ->assertRedirect();

    expect(Profile::current()->fresh()->logo_path)->toBeNull();
    Storage::disk('uploads')->assertMissing($profilePath);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toContain('uploads/'.$companyPath)
        ->not->toContain('footer-logo-icon');
});
