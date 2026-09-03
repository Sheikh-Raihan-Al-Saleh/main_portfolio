<?php

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;

test('a published landing page renders its visible sections in order', function () {
    $project = Project::factory()->create();
    $landingPage = ProjectLandingPage::factory()->for($project)->create();

    $second = LandingSection::factory()
        ->for($landingPage, 'landingPage')
        ->ofType(LandingSectionType::Faq)
        ->create(['sort_order' => 2, 'heading' => 'Questions']);

    $first = LandingSection::factory()
        ->for($landingPage, 'landingPage')
        ->ofType(LandingSectionType::Features)
        ->create(['sort_order' => 1, 'heading' => 'Highlights']);

    $this->get("/p/{$project->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('public/landing/Show')
            ->has('sections', 2)
            ->where('sections.0.id', $first->id)
            ->where('sections.1.id', $second->id)
            ->where('sections.0.type', 'features')
            ->where('landingPage.id', $landingPage->id),
        );
});

test('hidden sections are excluded from the landing page', function () {
    $project = Project::factory()->create();
    $landingPage = ProjectLandingPage::factory()->for($project)->create();

    LandingSection::factory()->for($landingPage, 'landingPage')->create();
    LandingSection::factory()->for($landingPage, 'landingPage')->hidden()->create();

    $this->get("/p/{$project->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('sections', 1));
});

test('an unpublished landing page is not found', function () {
    $project = Project::factory()->create();
    ProjectLandingPage::factory()->for($project)->draft()->create();

    $this->get("/p/{$project->slug}")->assertNotFound();
});

test('a landing page for an unpublished project is not found', function () {
    $project = Project::factory()->draft()->create();
    ProjectLandingPage::factory()->for($project)->create();

    $this->get("/p/{$project->slug}")->assertNotFound();
});

test('a project without a landing page has no landing route', function () {
    $project = Project::factory()->create();

    $this->get("/p/{$project->slug}")->assertNotFound();
});

test('richtext sections are rendered to sanitised html', function () {
    $project = Project::factory()->create();
    $landingPage = ProjectLandingPage::factory()->for($project)->create();

    LandingSection::factory()
        ->for($landingPage, 'landingPage')
        ->ofType(LandingSectionType::RichText)
        ->create([
            'data' => ['markdown' => "## Heading\n\n<script>alert(1)</script>\n\nBody text."],
        ]);

    $this->get("/p/{$project->slug}")
        ->assertOk()
        ->assertInertia(function ($page) {
            $html = $page->toArray()['props']['sections'][0]['body_html'];

            expect($html)
                ->toContain('<h2>Heading</h2>')
                ->and($html)->not->toContain('<script>');
        });
});

test('a published landing page exposes a landing url on the project', function () {
    $project = Project::factory()->create();
    ProjectLandingPage::factory()->for($project)->create();

    expect($project->fresh()->landing_url)->toBe("/p/{$project->slug}");
});

test('a draft landing page exposes no landing url', function () {
    $project = Project::factory()->create();
    ProjectLandingPage::factory()->for($project)->draft()->create();

    expect($project->fresh()->landing_url)->toBeNull();
});

test('saving a landing page flags the project as having one', function () {
    $project = Project::factory()->create();

    expect($project->has_landing_page)->toBeFalse();

    $landingPage = ProjectLandingPage::factory()->for($project)->create();

    expect($project->fresh()->has_landing_page)->toBeTrue();

    $landingPage->delete();

    expect($project->fresh()->has_landing_page)->toBeFalse();
});
