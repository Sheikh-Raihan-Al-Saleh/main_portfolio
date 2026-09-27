<?php

use App\Enums\ProjectContext;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('an admin can create a project', function () {
    $this->actingAs($this->admin)
        ->post('/admin/projects', [
            'title' => 'Realtime Analytics Dashboard',
            'context' => ProjectContext::Company->value,
            'summary' => 'Streaming event analytics.',
            'description' => "First paragraph.\n\nSecond paragraph.",
            'tech_stack' => ['Laravel', 'Vue'],
            'is_published' => true,
            'is_featured' => true,
        ])
        ->assertRedirect('/admin/projects');

    $project = Project::sole();

    expect($project->title)->toBe('Realtime Analytics Dashboard')
        ->and($project->slug)->toBe('realtime-analytics-dashboard')
        ->and($project->context)->toBe(ProjectContext::Company)
        ->and($project->tech_stack)->toBe(['Laravel', 'Vue'])
        ->and($project->is_published)->toBeTrue()
        ->and($project->is_featured)->toBeTrue();
});

test('a project requires a context', function () {
    $this->actingAs($this->admin)
        ->post('/admin/projects', ['title' => 'No Context'])
        ->assertSessionHasErrors('context');
});

test('the project form is given every context to choose from', function () {
    $project = Project::factory()->create(['context' => ProjectContext::Personal]);

    $this->actingAs($this->admin)
        ->get('/admin/projects/create')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/projects/Create')
            ->where('contexts', ProjectContext::options()),
        );

    $this->actingAs($this->admin)
        ->get("/admin/projects/{$project->slug}/edit")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/projects/Edit')
            ->where('contexts', ProjectContext::options())
            ->where('project.context', ProjectContext::Personal->value),
        );
});

test('a project can be moved between the company and personal contexts', function () {
    $project = Project::factory()->create(['context' => ProjectContext::Company]);

    $this->actingAs($this->admin)
        ->put("/admin/projects/{$project->slug}", [
            'title' => $project->title,
            'context' => ProjectContext::Personal->value,
        ])
        ->assertRedirect('/admin/projects');

    expect($project->fresh()->context)->toBe(ProjectContext::Personal);
});

test('slugs are made unique instead of colliding', function () {
    Project::factory()->create(['title' => 'Portfolio', 'slug' => 'portfolio']);

    $this->actingAs($this->admin)->post('/admin/projects', [
        'title' => 'Portfolio',
        'context' => ProjectContext::Company->value,
        'is_published' => true,
    ]);

    expect(Project::where('slug', 'portfolio-2')->exists())->toBeTrue();
});

test('a cover image is stored on the public disk', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)->post('/admin/projects', [
        'title' => 'With Cover',
        'context' => ProjectContext::Company->value,
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
    ]);

    $project = Project::sole();

    expect($project->cover_image_path)->not->toBeNull();
    Storage::disk('uploads')->assertExists($project->cover_image_path);
});

test('replacing a cover image deletes the previous file', function () {
    Storage::fake('uploads');

    $project = Project::factory()->create([
        'cover_image_path' => UploadedFile::fake()->image('old.jpg')->store('projects', 'public'),
    ]);
    $old = $project->cover_image_path;

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}", [
        'title' => $project->title,
        'context' => $project->context->value,
        'cover_image' => UploadedFile::fake()->image('new.jpg'),
    ]);

    Storage::disk('uploads')->assertMissing($old);
    Storage::disk('uploads')->assertExists($project->fresh()->cover_image_path);
});

test('deleting a project removes its uploaded images', function () {
    Storage::fake('uploads');

    $project = Project::factory()->create([
        'cover_image_path' => UploadedFile::fake()->image('cover.jpg')->store('projects', 'public'),
    ]);
    $path = $project->cover_image_path;

    $this->actingAs($this->admin)->delete("/admin/projects/{$project->slug}");

    expect(Project::count())->toBe(0);
    Storage::disk('uploads')->assertMissing($path);
});

test('a project requires a title', function () {
    $this->actingAs($this->admin)
        ->post('/admin/projects', ['title' => ''])
        ->assertSessionHasErrors('title');
});

test('a completion date cannot precede the start date', function () {
    $this->actingAs($this->admin)
        ->post('/admin/projects', [
            'title' => 'Backwards',
            'started_at' => '2025-06-01',
            'completed_at' => '2025-01-01',
        ])
        ->assertSessionHasErrors('completed_at');
});

test('reordering persists the new sort order', function () {
    $first = Project::factory()->create(['sort_order' => 0]);
    $second = Project::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->post('/admin/projects/reorder', ['ids' => [$second->id, $first->id]]);

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});
