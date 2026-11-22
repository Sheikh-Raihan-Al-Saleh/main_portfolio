<?php

use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\Technology;
use App\Services\Founder\FounderService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = admin();
});

// --- Skills -----------------------------------------------------------------

test('skills are listed by category and can be created in a founder category', function () {
    $category = app(FounderService::class)->current()->skillCategories()->first();

    $this->actingAs($this->admin)->post('/admin/skills', [
        'skill_category_id' => $category->id,
        'name' => 'Rust',
        'proficiency' => 70,
    ])->assertRedirect('/admin/skills');

    $this->actingAs($this->admin)->get('/admin/skills')
        ->assertInertia(fn ($page) => $page->has('categories', 6)->where('categories.0.skills.0.name', 'Rust'));
});

test('a skill cannot be put in an unknown category', function () {
    $this->actingAs($this->admin)->post('/admin/skills', [
        'skill_category_id' => 9999,
        'name' => 'Rust',
        'proficiency' => 70,
    ])->assertSessionHasErrors('skill_category_id');
});

test('categories can be added and renamed, and only empty ones deleted', function () {
    $this->actingAs($this->admin)->post('/admin/skill-categories', ['name' => 'Mobile'])->assertRedirect();
    $category = SkillCategory::query()->where('slug', 'mobile')->sole();

    $this->actingAs($this->admin)->put("/admin/skill-categories/{$category->id}", ['name' => 'Mobile apps']);
    expect($category->fresh()->slug)->toBe('mobile-apps');

    Skill::factory()->create(['skill_category_id' => $category->id]);
    $this->actingAs($this->admin)->delete("/admin/skill-categories/{$category->id}")->assertSessionHasErrors('category');

    $category->skills()->delete();
    $this->actingAs($this->admin)->delete("/admin/skill-categories/{$category->id}")->assertSessionHasNoErrors();
    expect(SkillCategory::query()->whereKey($category->id)->exists())->toBeFalse();
});

// --- Experience and education ------------------------------------------------

test('experience highlights are stored as ordered rows', function () {
    $this->actingAs($this->admin)->post('/admin/experiences', [
        'company' => 'Acme',
        'role' => 'Engineer',
        'start_date' => '2022-01-01',
        'highlights' => ['First', '', 'Second'],
    ])->assertRedirect('/admin/experiences');

    $experience = Experience::query()->with('highlights')->sole();
    expect($experience->highlights->pluck('body')->all())->toBe(['First', 'Second'])
        ->and($experience->is_current)->toBeTrue();

    $this->actingAs($this->admin)->put("/admin/experiences/{$experience->id}", [
        'company' => 'Acme',
        'role' => 'Lead',
        'start_date' => '2022-01-01',
        'end_date' => '2024-01-01',
        'highlights' => ['Only one'],
    ]);

    expect($experience->highlights()->pluck('body')->all())->toBe(['Only one']);
});

test('education entries can be created and deleted', function () {
    $this->actingAs($this->admin)->post('/admin/educations', ['institution' => 'MIT', 'degree' => 'BSc'])
        ->assertRedirect('/admin/educations');

    $education = Education::query()->sole();
    $this->actingAs($this->admin)->delete("/admin/educations/{$education->id}")->assertRedirect();

    expect(Education::query()->count())->toBe(0);
});

// --- Clients and technologies ------------------------------------------------

test('clients can be added with a logo, updated and removed', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)->post('/admin/clients', [
        'name' => 'Northwind',
        'is_visible' => '1',
        'logo' => UploadedFile::fake()->image('logo.png'),
    ])->assertRedirect();

    $client = Client::query()->sole();
    Storage::disk('uploads')->assertExists($client->logo_path);

    $this->actingAs($this->admin)->put("/admin/clients/{$client->id}", ['name' => 'Northwind Inc', 'remove_logo' => '1']);
    expect($client->fresh()->logo_path)->toBeNull();

    $this->actingAs($this->admin)->delete("/admin/clients/{$client->id}");
    expect(Client::query()->count())->toBe(0);
});

test('technologies are unique by slug', function () {
    $this->actingAs($this->admin)->post('/admin/technologies', ['name' => 'Vue.js'])->assertSessionHasNoErrors();
    $this->actingAs($this->admin)->post('/admin/technologies', ['name' => 'VUE.JS'])->assertSessionHasErrors('name');

    $this->actingAs($this->admin)->get('/admin/technologies')
        ->assertInertia(fn ($page) => $page->component('admin/technologies/Index')->has('technologies', 1));
});

// --- Reorder -----------------------------------------------------------------

test('a module list can be reordered through its own route', function () {
    [$a, $b, $c] = Project::factory()->count(3)->create()->all();

    $this->actingAs($this->admin)->post('/admin/projects/reorder', ['ids' => [$c->id, $a->id, $b->id]])->assertRedirect();

    expect(Project::query()->orderBy('sort_order')->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);
});

test('reordering blocks only moves blocks of the same page', function () {
    $first = ProjectLandingPage::factory()->create();
    $second = ProjectLandingPage::factory()->create();
    $x = LandingSection::factory()->create(['sectionable_id' => $first->id, 'sort_order' => 5]);
    $y = LandingSection::factory()->create(['sectionable_id' => $second->id, 'sort_order' => 5]);

    $this->actingAs($this->admin)->post('/admin/landing-sections/reorder', ['ids' => [$x->id, $y->id]])->assertRedirect();

    expect($x->fresh()->sort_order)->toBe(0)
        ->and($y->fresh()->sort_order)->toBe(5);
});

// --- Messages ----------------------------------------------------------------

test('opening a message marks it read and it can be marked unread again', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)->get('/admin/messages?filter=unread')
        ->assertInertia(fn ($page) => $page->has('messages.data', 1));

    $this->actingAs($this->admin)->get("/admin/messages/{$message->id}")->assertOk();
    expect($message->fresh()->read_at)->not->toBeNull();

    $this->actingAs($this->admin)->patch("/admin/messages/{$message->id}/unread")->assertRedirect('/admin/messages');
    expect($message->fresh()->read_at)->toBeNull();
});

// --- Dashboard ---------------------------------------------------------------

test('the dashboard reports stats, activity and completeness', function () {
    Project::factory()->count(2)->forStudio()->create();
    Project::factory()->forFounder()->draft()->create();
    ContactMessage::factory()->count(2)->create();
    Technology::factory()->create();

    $this->actingAs($this->admin)->get('/admin')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/dashboard/Index')
            ->where('stats.projects', 3)
            ->where('stats.publishedProjects', 2)
            ->where('stats.unreadMessages', 2)
            ->has('activity.labels', 30)
            ->has('completeness.studio.percent')
            ->has('recentMessages', 2),
        );
});
