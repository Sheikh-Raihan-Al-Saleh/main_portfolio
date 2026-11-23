<?php

use App\Http\Resources\Founder\FounderResource;
use App\Services\Founder\FounderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('the founder form loads with merged page copy', function () {
    $this->actingAs(admin())
        ->get('/admin/founder')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/founder/Edit')
            ->has('founder.content.sections.about')
            ->has('socials'),
        );
});

test('an admin can update the founder profile, avatar and resume', function () {
    Storage::fake('uploads');

    $this->actingAs(admin())
        ->put('/admin/founder', [
            'name' => 'Jo Founder',
            'roles' => ['Engineer', 'Builder'],
            'avatar' => UploadedFile::fake()->image('me.jpg'),
            'resume' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'socials' => ['github' => 'https://github.com/jo'],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $founder = app(FounderService::class)->current()->fresh('socialLinks');

    expect($founder->name)->toBe('Jo Founder')
        ->and($founder->roles)->toBe(['Engineer', 'Builder'])
        ->and($founder->socialLinks)->toHaveCount(1)
        ->and(FounderResource::resumeUrl($founder))->toBe('/resume');

    Storage::disk('uploads')->assertExists([$founder->avatar_path, $founder->resume_path]);
});

test('a new founder starts with the default skill categories', function () {
    expect(app(FounderService::class)->current()->skillCategories()->count())->toBe(6);
});
