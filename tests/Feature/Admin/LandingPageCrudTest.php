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
