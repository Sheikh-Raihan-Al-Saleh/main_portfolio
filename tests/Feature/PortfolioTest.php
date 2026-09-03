<?php

use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;

test('the landing page renders with the profile and published content', function () {
    Profile::current()->update(['name' => 'Ada Lovelace', 'headline' => 'Software Engineer']);
    $published = Project::factory()->create();
    $draft = Project::factory()->draft()->create();
    Skill::factory()->create();
    Experience::factory()->current()->create();
    Education::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->where('profile.name', 'Ada Lovelace')
            ->has('projects', 1)
            ->has('skillGroups')
            ->has('experiences', 1)
            ->has('educations', 1)
            ->where('projects.0.id', $published->id),
        );

    expect($draft->is_published)->toBeFalse();
});

test('the landing page works with an empty database', function () {
    $this->get('/')->assertOk();
});

test('unpublished projects are hidden from the archive', function () {
    $published = Project::factory()->create();
    Project::factory()->draft()->create();

    $this->get('/projects')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Projects')
            ->has('projects', 1)
            ->where('projects.0.id', $published->id),
        );
});

test('the archive seeds the technology filter from the query string', function () {
    Project::factory()->create(['tech_stack' => ['Laravel', 'Vue']]);
    Project::factory()->create(['tech_stack' => ['Go']]);

    // Filtering itself happens client-side, so the server still sends every
    // published project and only nominates which tag starts selected.
    $this->get('/projects?tech=Go')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('projects', 2)
            ->where('filters.tech', 'Go')
            ->where('technologies', ['Go', 'Laravel', 'Vue']),
        );
});

test('the archive ignores a technology filter no project carries', function () {
    Project::factory()->create(['tech_stack' => ['Laravel']]);

    $this->get('/projects?tech=Cobol')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('filters.tech', null));
});

test('a published project detail page renders', function () {
    $project = Project::factory()->create(['title' => 'Ahad ERP']);

    $this->get("/projects/{$project->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/ProjectShow')
            ->where('project.title', 'Ahad ERP'),
        );
});

test('an unpublished project detail page returns 404', function () {
    $project = Project::factory()->draft()->create();

    $this->get("/projects/{$project->slug}")->assertNotFound();
});

test('the resume route 404s when no resume has been uploaded', function () {
    $this->get('/resume')->assertNotFound();
});
