<?php

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Services\Studio\StudioService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->admin = admin();
});

test('the builder renders before the page exists', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->admin)->get("/admin/projects/{$project->slug}/landing")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/projects/landing/Edit')
            ->where('landingPage', null)
            ->has('sectionTypes'),
        );
});

test('saving creates the page once and updates it afterwards', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}/landing", ['headline' => 'Old', 'is_published' => '1'])
        ->assertRedirect();
    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}/landing", ['headline' => 'New'])
        ->assertRedirect();

    expect(ProjectLandingPage::query()->count())->toBe(1)
        ->and($project->landingPage()->value('headline'))->toBe('New');
});

test('an invalid accent colour is rejected', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->admin)
        ->put("/admin/projects/{$project->slug}/landing", ['accent_from' => 'red'])
        ->assertSessionHasErrors('accent_from');
});

test('hero slots are stored by position and can be cleared one by one', function () {
    $project = Project::factory()->create();

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}/landing", [
        'hero_media' => [2 => UploadedFile::fake()->image('phone.png')],
    ])->assertSessionHasNoErrors();

    $page = $project->landingPage()->with('heroMedia')->first();
    expect($page->heroMedia->pluck('slot')->all())->toBe([2]);
    $phone = $page->heroMedia->first()->path;

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}/landing", [
        'hero_media' => [0 => UploadedFile::fake()->image('desktop.png')],
    ]);
    expect($page->heroMedia()->pluck('slot')->all())->toBe([0, 2]);

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}/landing", [
        'remove_hero_media' => [2 => '1'],
    ]);
    expect($page->heroMedia()->pluck('slot')->all())->toBe([0]);
    Storage::disk('uploads')->assertMissing($phone);
});

test('deleting the landing page removes its blocks and files', function () {
    $page = ProjectLandingPage::factory()->create(['og_image_path' => 'landing/og/x.png']);
    Storage::disk('uploads')->put('landing/og/x.png', 'x');
    LandingSection::factory()->create(['sectionable_id' => $page->id]);

    $this->actingAs($this->admin)->delete("/admin/projects/{$page->project->slug}/landing")
        ->assertRedirect('/admin/projects');

    expect(ProjectLandingPage::query()->count())->toBe(0)
        ->and(LandingSection::query()->count())->toBe(0);
    Storage::disk('uploads')->assertMissing('landing/og/x.png');
});

test('blocks can be added to the studio home and to a project page', function () {
    $project = Project::factory()->create();
    $data = ['items' => [['icon' => 'Zap', 'title' => 'Fast', 'text' => 'Very']]];

    $this->actingAs($this->admin)->post('/admin/landing-sections', [
        'owner' => 'studio', 'type' => 'features', 'heading' => 'Home', 'data' => $data,
    ])->assertSessionHasNoErrors();

    // The project page does not exist yet: adding a block creates it.
    $this->actingAs($this->admin)->post('/admin/landing-sections', [
        'owner' => 'project', 'project_id' => $project->id, 'type' => 'features', 'data' => $data,
    ])->assertSessionHasNoErrors();

    expect(app(StudioService::class)->current()->sections()->count())->toBe(1)
        ->and($project->landingPage()->firstOrFail()->sections()->count())->toBe(1);
});

test('a block validates its own data shape', function () {
    $this->actingAs($this->admin)->post('/admin/landing-sections', [
        'owner' => 'studio', 'type' => 'demo_embed', 'data' => ['url' => 'http://insecure.example', 'aspect' => '16/9', 'chrome' => 'none'],
    ])->assertSessionHasErrors('data.url');
});

test('editing a block deletes media it no longer uses', function () {
    $page = ProjectLandingPage::factory()->create();
    Storage::disk('uploads')->put('landing/gallery/a.png', 'x');
    Storage::disk('uploads')->put('landing/gallery/b.png', 'x');
    $section = LandingSection::factory()->ofType(LandingSectionType::Gallery)->create([
        'sectionable_id' => $page->id,
        'data' => ['images' => ['landing/gallery/a.png', 'landing/gallery/b.png']],
    ]);

    $this->actingAs($this->admin)->put("/admin/landing-sections/{$section->id}", [
        'data' => ['images' => ['landing/gallery/a.png']],
    ])->assertSessionHasNoErrors();

    Storage::disk('uploads')->assertExists('landing/gallery/a.png');
    Storage::disk('uploads')->assertMissing('landing/gallery/b.png');
});

test('block media uploads return the stored path and relative url', function () {
    $this->actingAs($this->admin)
        ->post('/admin/landing-sections/media', [
            'file' => UploadedFile::fake()->image('step.png'),
            'directory' => 'steps',
        ])
        ->assertOk()
        ->assertJson(fn ($json) => $json
            ->where('path', fn (string $path) => str_starts_with($path, 'landing/steps/'))
            ->where('url', fn (string $url) => str_starts_with($url, '/uploads/landing/steps/')),
        );
});
