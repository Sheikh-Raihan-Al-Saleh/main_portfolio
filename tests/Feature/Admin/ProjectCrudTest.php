<?php

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
        ->and($project->tech_stack)->toBe(['Laravel', 'Vue'])
        ->and($project->is_published)->toBeTrue()
        ->and($project->is_featured)->toBeTrue();
});

test('slugs are made unique instead of colliding', function () {
    Project::factory()->create(['title' => 'Portfolio', 'slug' => 'portfolio']);

    $this->actingAs($this->admin)
        ->post('/admin/projects', ['title' => 'Portfolio', 'is_published' => true]);

    expect(Project::where('slug', 'portfolio-2')->exists())->toBeTrue();
});

test('a cover image is stored on the public disk', function () {
    Storage::fake('public');

    $this->actingAs($this->admin)->post('/admin/projects', [
        'title' => 'With Cover',
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
    ]);

    $project = Project::sole();

    expect($project->cover_image_path)->not->toBeNull();
    Storage::disk('public')->assertExists($project->cover_image_path);
});

test('replacing a cover image deletes the previous file', function () {
    Storage::fake('public');

    $project = Project::factory()->create([
        'cover_image_path' => UploadedFile::fake()->image('old.jpg')->store('projects', 'public'),
    ]);
    $old = $project->cover_image_path;

    $this->actingAs($this->admin)->put("/admin/projects/{$project->slug}", [
        'title' => $project->title,
        'cover_image' => UploadedFile::fake()->image('new.jpg'),
    ]);

    Storage::disk('public')->assertMissing($old);
    Storage::disk('public')->assertExists($project->fresh()->cover_image_path);
});

test('deleting a project removes its uploaded images', function () {
    Storage::fake('public');

    $project = Project::factory()->create([
        'cover_image_path' => UploadedFile::fake()->image('cover.jpg')->store('projects', 'public'),
    ]);
    $path = $project->cover_image_path;

    $this->actingAs($this->admin)->delete("/admin/projects/{$project->slug}");

    expect(Project::count())->toBe(0);
    Storage::disk('public')->assertMissing($path);
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
