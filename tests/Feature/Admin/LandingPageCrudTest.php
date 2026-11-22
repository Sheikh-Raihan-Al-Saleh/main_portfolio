<?php

use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->project = Project::factory()->create();
});

test('a non-admin cannot open the landing page builder', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get("/admin/projects/{$this->project->slug}/landing")
        ->assertForbidden();
});

test('a guest is redirected away from the landing page builder', function () {
    $this->get("/admin/projects/{$this->project->slug}/landing")
        ->assertRedirect('/login');
});

test('an admin can open the builder for a project with no landing page', function () {
    $this->actingAs($this->admin)
        ->get("/admin/projects/{$this->project->slug}/landing")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('admin/projects/landing/Edit')
            ->where('landingPage', null)
            ->has('sections', 0)
            ->has('sectionTypes'),
        );
});

test('an admin can create a landing page and it flags the project', function () {
    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'eyebrow' => 'Case study',
            'headline' => 'A cinematic rendering platform',
            'subheadline' => 'Built for high-definition FPV footage.',
            'accent_from' => '#6366f1',
            'accent_to' => '#a855f7',
            'is_published' => true,
        ])
        ->assertRedirect("/admin/projects/{$this->project->slug}/landing");

    $landingPage = ProjectLandingPage::sole();

    expect($landingPage->headline)->toBe('A cinematic rendering platform')
        ->and($landingPage->is_published)->toBeTrue()
        ->and($this->project->fresh()->has_landing_page)->toBeTrue();
});

test('an invalid accent colour is rejected', function () {
    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Test',
            'accent_from' => 'not-a-colour',
        ])
        ->assertSessionHasErrors('accent_from');
});

test('hero media is stored on the public disk', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Test',
            'hero_media' => UploadedFile::fake()->image('hero.jpg'),
        ]);

    $landingPage = ProjectLandingPage::sole();

    expect($landingPage->hero_media_path)->not->toBeNull();
    Storage::disk('uploads')->assertExists($landingPage->hero_media_path);
});

test('updating a landing page replaces rather than duplicates it', function () {
    ProjectLandingPage::factory()->for($this->project)->create(['headline' => 'Old']);

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", ['headline' => 'New']);

    expect(ProjectLandingPage::count())->toBe(1)
        ->and(ProjectLandingPage::sole()->headline)->toBe('New');
});

test('deleting a landing page clears the project flag and its media', function () {
    Storage::fake('uploads');

    $landingPage = ProjectLandingPage::factory()->for($this->project)->create([
        'hero_media_path' => 'landing/hero/example.jpg',
    ]);

    Storage::disk('uploads')->put('landing/hero/example.jpg', 'x');

    $this->actingAs($this->admin)
        ->delete("/admin/projects/{$this->project->slug}/landing")
        ->assertRedirect('/admin/projects');

    expect(ProjectLandingPage::find($landingPage->id))->toBeNull()
        ->and($this->project->fresh()->has_landing_page)->toBeFalse();

    Storage::disk('uploads')->assertMissing('landing/hero/example.jpg');
});

test('replacing hero slot 0 clears the legacy hero path that shared its file', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('landing/hero/legacy.png', 'x');

    ProjectLandingPage::factory()->for($this->project)->create([
        'hero_media_path' => 'landing/hero/legacy.png',
        'hero_media_paths' => ['landing/hero/legacy.png'],
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Test',
            'hero_media_paths' => [UploadedFile::fake()->image('new.jpg')],
        ]);

    $landingPage = ProjectLandingPage::sole();

    expect($landingPage->hero_media_path)->toBeNull()
        ->and(array_filter($landingPage->hero_media_paths))->toHaveCount(1);
    Storage::disk('uploads')->assertMissing('landing/hero/legacy.png');
    Storage::disk('uploads')->assertExists($landingPage->hero_media_paths[0]);
});

test('hero slots keep their positions when an earlier slot is empty', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Test',
            'hero_media_paths' => [0 => UploadedFile::fake()->image('desktop.jpg'), 2 => UploadedFile::fake()->image('phone.jpg')],
        ])
        ->assertRedirect();

    $paths = ProjectLandingPage::sole()->hero_media_paths;

    // The phone image stays in the phone slot instead of sliding into slot 1.
    expect($paths)->toHaveCount(3)
        ->and($paths[0])->not->toBeNull()
        ->and($paths[1])->toBeNull()
        ->and($paths[2])->not->toBeNull();
});

test('a hero slot can be cleared on its own', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('landing/hero/a.png', 'x');
    Storage::disk('uploads')->put('landing/hero/b.png', 'x');

    ProjectLandingPage::factory()->for($this->project)->create([
        'hero_media_path' => null,
        'hero_media_paths' => ['landing/hero/a.png', 'landing/hero/b.png', null],
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Test',
            'remove_hero_media_slots' => [0 => 0, 1 => 1, 2 => 0],
        ])
        ->assertRedirect();

    expect(ProjectLandingPage::sole()->hero_media_paths)->toBe(['landing/hero/a.png', null, null]);
    Storage::disk('uploads')->assertExists('landing/hero/a.png');
    Storage::disk('uploads')->assertMissing('landing/hero/b.png');
});

test('saving without touching the slots leaves them alone', function () {
    ProjectLandingPage::factory()->for($this->project)->create([
        'hero_media_paths' => ['landing/hero/a.png', null, 'landing/hero/c.png'],
    ]);

    $this->actingAs($this->admin)
        ->post("/admin/projects/{$this->project->slug}/landing", [
            'headline' => 'Renamed',
            'remove_hero_media_slots' => [0, 0, 0],
        ])
        ->assertRedirect();

    expect(ProjectLandingPage::sole()->hero_media_paths)
        ->toBe(['landing/hero/a.png', null, 'landing/hero/c.png']);
});
