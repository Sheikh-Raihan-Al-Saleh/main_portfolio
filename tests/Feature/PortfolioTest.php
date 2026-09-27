<?php

use App\Enums\ProjectContext;
use App\Models\Client;
use App\Models\Company;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;

test('the company home page renders with the company and its work', function () {
    Company::current()->update(['name' => 'Sheikh Nabil', 'headline' => 'Software studio']);
    $published = Project::factory()->companyWork()->create();
    Project::factory()->companyWork()->draft()->create();
    Project::factory()->personalWork()->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->where('company.name', 'Sheikh Nabil')
            ->has('sections')
            // Personal work is deliberately kept off the company home page.
            ->has('projects', 1)
            ->where('projects.0.id', $published->id),
        );
});

test('the company home page works with an empty database', function () {
    $this->get('/')->assertOk();
});

test('the home page previews the founder\'s own work and links to his portfolio', function () {
    $personal = Project::factory()->personalWork()->create(['title' => 'Side project']);
    Project::factory()->companyWork()->create(['title' => 'Client work']);
    Project::factory()->personalWork()->draft()->create(['title' => 'Unfinished']);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->has('personalProjects', 1)
            ->where('personalProjects.0.id', $personal->id)
            ->where('totalPersonalProjects', 1)
            // The company archive must not leak into the personal preview.
            ->where('personalProjects.0.title', 'Side project'),
        );
});

test('the home page personal preview is capped at three projects', function () {
    Project::factory()->count(4)->personalWork()->create();

    $this->get('/')
        ->assertInertia(fn ($page) => $page
            ->has('personalProjects', 3)
            ->where('totalPersonalProjects', 4),
        );
});

test('the founder portfolio page renders his personal work', function () {
    Profile::current()->update([
        'name' => 'Ada Lovelace',
        'headline' => 'Software Engineer',
        'founder_message' => "I started the studio because\n\ngood software deserves better.",
    ]);
    $personal = Project::factory()->personalWork()->create();
    Project::factory()->companyWork()->create();
    Skill::factory()->create();
    Experience::factory()->current()->create();
    Education::factory()->create();

    $this->get('/founder')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Founder')
            ->where('profile.name', 'Ada Lovelace')
            ->where('profile.founder_message', "I started the studio because\n\ngood software deserves better.")
            ->has('skillGroups')
            ->has('experiences', 1)
            ->has('educations', 1)
            ->has('projects', 1)
            ->where('projects.0.id', $personal->id),
        );
});

test('the founder portfolio renders its real figures in the html, not placeholders', function () {
    Project::factory()->count(2)->personalWork()->create();
    Skill::factory()->count(3)->create();
    Experience::factory()->create(['start_date' => now()->subYears(6)]);

    $html = $this->get('/founder')->assertOk()->getContent();

    // The hero counters used to be server-rendered as 0 and only filled in once
    // they scrolled into view, so crawlers and no-JS visitors were told the
    // portfolio had no projects and no skills.
    expect($html)->toMatch('/tabular-nums[^>]*>\s*6\+\s*<\/span>\s*<\/span>\s*<span[^>]*>\s*years\s*<\/span>/');
    expect($html)->toMatch('/tabular-nums[^>]*>\s*2\s*<\/span>\s*<\/span>\s*<span[^>]*>\s*projects\s*<\/span>/');
    expect($html)->toMatch('/tabular-nums[^>]*>\s*3\s*<\/span>\s*<\/span>\s*<span[^>]*>\s*skills\s*<\/span>/');
});

test('the public header and footer render the uploaded company logo', function () {
    Company::current()->update(['logo_path' => 'logos/brand.png']);

    foreach (['/', '/about', '/founder'] as $path) {
        $html = $this->get($path)->assertOk()->getContent();

        expect($html)->toContain('uploads/logos/brand.png');
        // The initials fallback must step aside once a logo exists.
        expect($html)->not->toContain('footer-logo-icon');
    }
});

test('the public header and footer fall back to the initials mark without a logo', function () {
    Company::current()->update(['logo_path' => null]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)->not->toContain('uploads/logos');
    expect($html)->toContain('footer-logo-icon');
});

test('the company about page renders the studio and links to the founder', function () {
    Company::current()->update([
        'name' => 'Sheikh Nabil',
        'bio' => "First paragraph.\n\nSecond paragraph.",
        'mission' => 'Software our clients can own outright.',
    ]);
    Profile::current()->update(['name' => 'Ada Lovelace']);
    $client = Client::factory()->create(['name' => 'Northwind']);

    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/About')
            ->where('company.name', 'Sheikh Nabil')
            ->where('profile.name', 'Ada Lovelace')
            ->missing('sections')
            ->has('clients', 1)
            ->where('clients.0.name', 'Northwind')
            ->where('stats.clients', 1),
        );
});

test('the about page does not repeat the home page section blocks', function () {
    $company = Company::current();
    $section = $company->sections()->create([
        'type' => 'features',
        'title' => 'What we build',
        'is_visible' => true,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->has('sections', 1)
            ->where('sections.0.id', $section->id),
        );

    // The About page has chapters of its own, so the home page's blocks are
    // never rendered there a second time.
    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/About')
            ->missing('sections'),
        );
});

test('hidden clients are kept off the public pages', function () {
    Client::factory()->create(['name' => 'Visible Co', 'is_visible' => true]);
    Client::factory()->create(['name' => 'Hidden Co', 'is_visible' => false]);

    foreach (['/', '/about'] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component($url === '/' ? 'public/Home' : 'public/About')
                ->has('clients', 1)
                ->where('clients.0.name', 'Visible Co'),
            );
    }
});

test('the public pages never claim zero clients or zero projects', function () {
    // Nothing is published and every client is hidden, so there is no claim to
    // make. The figures come back as null and the sections drop them, rather
    // than printing "0 clients served" on the live site.
    Client::factory()->create(['is_visible' => false]);
    Project::factory()->companyWork()->draft()->create();

    foreach (['/', '/about'] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('stats.clients', null)
                ->where('stats.projects', null),
            );
    }
});

test('the founder page shows every personal project', function () {
    $personal = Project::factory()->count(8)->personalWork()->create();
    Project::factory()->companyWork()->create();

    $response = $this->get('/founder')->assertOk();

    $ids = collect($response->viewData('page')['props']['projects'])
        ->pluck('id')
        ->all();

    expect($ids)->toEqualCanonicalizing($personal->modelKeys());
});

test('unpublished projects are hidden from the archive', function () {
    $published = Project::factory()->companyWork()->create();
    Project::factory()->companyWork()->draft()->create();
    Project::factory()->personalWork()->create();

    $this->get('/projects')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Projects')
            ->has('projects', 1)
            ->where('projects.0.id', $published->id),
        );
});

test('the archive seeds the technology filter from the query string', function () {
    Project::factory()->companyWork()->create(['tech_stack' => ['Laravel', 'Vue']]);
    Project::factory()->companyWork()->create(['tech_stack' => ['Go']]);

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
    Project::factory()->companyWork()->create(['tech_stack' => ['Laravel']]);

    $this->get('/projects?tech=Cobol')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('filters.tech', null));
});

test('related projects stay within the same context', function () {
    $project = Project::factory()->personalWork()->create(['title' => 'Ahad ERP']);
    Project::factory()->personalWork()->create();
    Project::factory()->companyWork()->create();

    $this->get("/projects/{$project->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/ProjectShow')
            ->where('project.title', 'Ahad ERP')
            ->where('project.context', ProjectContext::Personal->value)
            ->has('related', 1),
        );
});

test('a published project detail page renders', function () {
    $project = Project::factory()->companyWork()->create(['title' => 'Ahad ERP']);

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
