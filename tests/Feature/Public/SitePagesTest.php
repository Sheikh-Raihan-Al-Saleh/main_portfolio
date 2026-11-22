<?php

use App\Models\Client;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\Skill;
use App\Services\Founder\FounderService;
use App\Services\Studio\StudioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

test('the studio home page shows studio work, founder work, clients and blocks', function () {
    $studio = app(StudioService::class)->current();
    Project::factory()->count(2)->forStudio()->create();
    Project::factory()->forStudio()->draft()->create();
    Project::factory()->forFounder()->create();
    Client::factory()->create();
    Client::factory()->hidden()->create();
    LandingSection::factory()->onStudio($studio)->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/home/Index')
            ->has('projects', 2)
            ->where('totalProjects', 2)
            ->has('founderProjects', 1)
            ->has('clients', 1)
            ->has('sections', 1)
            ->has('content.capabilities')
            ->has('projects.0', fn ($project) => $project
                ->where('owner', 'studio')
                ->whereType('technologies', 'array')
                ->where('url', fn (string $url) => str_starts_with($url, '/projects/'))
                ->etc(),
            ),
        );
});

test('the about page renders the studio story and clients', function () {
    Client::factory()->count(2)->create();

    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/about/Index')
            ->has('clients', 2)
            ->where('stats.clients', 2),
        );
});

test('the founder page shows only personal work and the full history', function () {
    Project::factory()->count(2)->forFounder()->create();
    Project::factory()->forStudio()->create();
    Skill::factory()->count(3)->create();
    Experience::factory()->create()->highlights()->createMany([
        ['body' => 'Shipped it', 'sort_order' => 0],
    ]);
    Education::factory()->create();

    $this->get('/founder')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/founder/Index')
            ->has('projects', 2)
            ->has('skillGroups', 1)
            ->has('skillGroups.0.skills', 3)
            ->has('experiences.0.highlights', 1)
            ->has('educations', 1)
            ->where('stats.projects', 2)
            ->where('stats.skills', 3),
        );
});

test('the archive lists every published project and seeds the filters', function () {
    Project::factory()->forStudio()->create();
    Project::factory()->forFounder()->create();
    Project::factory()->draft()->create();

    $this->get('/projects?tech=laravel&owner=founder')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/projects/Index')
            ->has('projects', 2)
            ->where('filters.tech', 'laravel')
            ->where('filters.owner', 'founder'),
        );

    $this->get('/projects?owner=hacker')
        ->assertInertia(fn ($page) => $page->where('filters.owner', null));
});

test('a project page shows the gallery and related work', function () {
    $project = Project::factory()->forStudio()->create();
    $project->images()->create(['path' => 'projects/gallery/a.jpg', 'sort_order' => 0]);
    Project::factory()->forStudio()->create();

    $this->get("/projects/{$project->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/projects/Show')
            ->where('project.slug', $project->slug)
            ->where('project.images.0.url', '/uploads/projects/gallery/a.jpg')
            ->has('related', 1),
        );
});

test('draft projects return 404', function () {
    $project = Project::factory()->draft()->create();

    $this->get("/projects/{$project->slug}")->assertNotFound();
    $this->get("/p/{$project->slug}")->assertNotFound();
});

test('a published landing page renders its visible blocks in order', function () {
    $page = ProjectLandingPage::factory()->create();
    LandingSection::factory()->create(['sectionable_id' => $page->id, 'heading' => 'Second', 'sort_order' => 2]);
    LandingSection::factory()->create(['sectionable_id' => $page->id, 'heading' => 'First', 'sort_order' => 1]);
    LandingSection::factory()->hidden()->create(['sectionable_id' => $page->id]);
    $page->heroMedia()->create(['slot' => 2, 'path' => 'landing/hero/phone.png']);

    $this->get("/p/{$page->project->slug}")
        ->assertOk()
        ->assertInertia(fn ($inertia) => $inertia
            ->component('public/landing/Show')
            ->has('sections', 2)
            ->where('sections.0.heading', 'First')
            ->where('landingPage.hero_media', [null, null, '/uploads/landing/hero/phone.png']),
        );
});

test('an unpublished landing page returns 404', function () {
    $page = ProjectLandingPage::factory()->draft()->create();

    $this->get("/p/{$page->project->slug}")->assertNotFound();
});

test('rich text blocks are rendered to sanitised html', function () {
    $page = ProjectLandingPage::factory()->create();
    LandingSection::factory()->create([
        'sectionable_id' => $page->id,
        'type' => 'richtext',
        'data' => ['markdown' => "## Title\n\n<script>alert(1)</script>"],
    ]);

    $this->get("/p/{$page->project->slug}")
        ->assertInertia(fn ($inertia) => $inertia
            ->where('sections.0.body_html', fn (string $html) => str_contains($html, '<h2>Title</h2>') && ! str_contains($html, '<script>')),
        );
});

test('a published card links to its landing page only when the page is published', function () {
    $published = ProjectLandingPage::factory()->create();
    ProjectLandingPage::factory()->draft()->create();

    $this->get('/projects')
        ->assertInertia(fn ($page) => $page
            ->where('projects', fn ($projects) => collect($projects)->whereNotNull('landing_url')->pluck('slug')->all() === [$published->project->slug]),
        );
});

test('the resume streams the uploaded file and 404s without one', function () {
    Storage::fake('uploads');

    $this->get('/resume')->assertNotFound();

    Storage::disk('uploads')->put('founder/resume/cv.pdf', '%PDF-1.4');
    app(FounderService::class)->current()->forceFill(['resume_path' => 'founder/resume/cv.pdf'])->save();

    $response = $this->get('/resume')->assertOk();

    expect($response->headers->get('content-disposition'))->toContain('resume.pdf');
});

test('the brand is shared on public, auth and admin pages', function (string $uri, bool $asAdmin) {
    app(StudioService::class)->update(['name' => 'Acme Studio']);

    $response = $asAdmin ? $this->actingAs(admin())->get($uri) : $this->get($uri);

    $response->assertOk()->assertInertia(fn ($page) => $page
        ->where('brand.name', 'Acme Studio')
        ->has('brand.logo_url')
        ->has('brand.logo_mark_url'),
    );
})->with([
    'home' => ['/', false],
    'founder' => ['/founder', false],
    'login' => ['/login', false],
    'admin' => ['/admin', true],
]);

test('public pages run a fixed number of queries however much content exists', function (string $uri) {
    $measure = function () use ($uri): int {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get($uri)->assertOk();

        return count(DB::getQueryLog());
    };

    Project::factory()->forStudio()->create();
    Project::factory()->forFounder()->create();
    Skill::factory()->create();
    Experience::factory()->create();
    $small = $measure();

    Project::factory()->count(5)->forStudio()->create();
    Project::factory()->count(5)->forFounder()->create();
    Skill::factory()->count(5)->create();
    Experience::factory()->count(3)->create();
    Client::factory()->count(4)->create();
    $large = $measure();

    expect($large)->toBe($small);
})->with(['/', '/founder', '/projects', '/about']);
