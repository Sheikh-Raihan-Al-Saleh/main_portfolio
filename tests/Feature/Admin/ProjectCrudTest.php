<?php

use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Technology;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
    $this->admin = admin();
});

test('the project list and forms load', function () {
    Project::factory()->count(2)->create();

    $this->actingAs($this->admin)->get('/admin/projects')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('project/ProjectIndex')->has('projects', 2));

    $this->actingAs($this->admin)->get('/admin/projects/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('owners', 2)->has('technologyOptions'));
});

test('the project list can be searched', function () {
    Project::factory()->create(['title' => 'Alpha ERP']);
    Project::factory()->create(['title' => 'Beta Shop']);

    $this->actingAs($this->admin)->get('/admin/projects?search=ERP')
        ->assertInertia(fn ($page) => $page->has('projects', 1)->where('projects.0.title', 'Alpha ERP'));
});

test('an admin can create a project with technologies, cover and gallery', function () {
    Technology::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->actingAs($this->admin)
        ->post('/admin/projects', [
            'owner' => 'founder',
            'title' => 'My Side Project',
            'summary' => 'A thing',
            'is_published' => '1',
            'tech_stack' => ['laravel', 'Vue.js'],
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'gallery' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ])
        ->assertRedirect('/admin/projects')
        ->assertSessionHasNoErrors();

    $project = Project::with(['technologies', 'images'])->sole();

    expect($project->owner_type)->toBe('founder')
        ->and($project->slug)->toBe('my-side-project')
        ->and($project->published_at)->not->toBeNull()
        ->and($project->technologies->pluck('slug')->all())->toBe(['laravel', 'vuejs'])
        ->and($project->images)->toHaveCount(2)
        ->and(Technology::count())->toBe(2);

    Storage::disk('uploads')->assertExists($project->cover_image_path);
});

test('slugs stay unique', function () {
    Project::factory()->create(['slug' => 'shop']);

    $this->actingAs($this->admin)->post('/admin/projects', ['owner' => 'studio', 'title' => 'Shop'])->assertSessionHasNoErrors();

    expect(Project::latest('id')->value('slug'))->toBe('shop-2');
});

test('updating a project removes ticked gallery images and syncs technologies', function () {
    $project = Project::factory()->create();
    Storage::disk('uploads')->put('projects/gallery/old.jpg', 'x');
    $image = $project->images()->create(['path' => 'projects/gallery/old.jpg', 'sort_order' => 0]);

    $this->actingAs($this->admin)
        ->put("/admin/projects/{$project->slug}", [
            'owner' => 'studio',
            'title' => $project->title,
            'tech_stack' => ['Go'],
            'remove_gallery' => [$image->id],
        ])
        ->assertRedirect('/admin/projects');

    expect($project->images()->count())->toBe(0)
        ->and($project->technologies()->pluck('name')->all())->toBe(['Go']);
    Storage::disk('uploads')->assertMissing('projects/gallery/old.jpg');
});

test('a forged gallery removal cannot touch another project', function () {
    $mine = Project::factory()->create();
    $theirs = Project::factory()->create();
    $image = $theirs->images()->create(['path' => 'projects/gallery/x.jpg', 'sort_order' => 0]);

    $this->actingAs($this->admin)->put("/admin/projects/{$mine->slug}", [
        'owner' => 'studio',
        'title' => $mine->title,
        'remove_gallery' => [$image->id],
    ]);

    expect($theirs->images()->count())->toBe(1);
});

test('deleting a project removes its landing page, blocks and files', function () {
    $page = ProjectLandingPage::factory()->create();
    $project = $page->project;
    LandingSection::factory()->count(2)->create(['sectionable_id' => $page->id]);
    Storage::disk('uploads')->put('projects/cover.jpg', 'x');
    $project->update(['cover_image_path' => 'projects/cover.jpg']);

    $this->actingAs($this->admin)->delete("/admin/projects/{$project->slug}")->assertRedirect();

    expect(Project::count())->toBe(0)
        ->and(ProjectLandingPage::count())->toBe(0)
        ->and(LandingSection::count())->toBe(0);
    Storage::disk('uploads')->assertMissing('projects/cover.jpg');
});
