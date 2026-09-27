<?php

use App\Enums\ProjectContext;
use App\Models\Company;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Project;
use Database\Seeders\PortfolioSeeder;

test('the seeder splits the demo projects across both contexts', function () {
    $this->seed(PortfolioSeeder::class);

    expect(Project::companyWork()->count())->toBe(3)
        ->and(Project::personalWork()->count())->toBe(3);
});

test('re-running the seeder repairs contexts without touching edited copy', function () {
    $this->seed(PortfolioSeeder::class);

    $project = Project::query()->where('title', 'Ahad ERP')->sole();

    // Simulate a row written before the context column existed, plus a local edit.
    $project->update([
        'context' => ProjectContext::Personal,
        'summary' => 'Hand written summary.',
    ]);

    $this->seed(PortfolioSeeder::class);

    $project->refresh();

    expect($project->context)->toBe(ProjectContext::Company)
        ->and($project->summary)->toBe('Hand written summary.');
});

test('the seeder gives the company home a starter set of sections', function () {
    $this->seed(PortfolioSeeder::class);

    $company = Company::current();
    $sections = $company->sections()->ordered()->get();

    expect($sections)->toHaveCount(4)
        ->and($sections->pluck('type')->map->value->all())->toBe([
            'features',
            'stats',
            'faq',
            'cta',
        ])
        ->and($sections->pluck('sort_order')->all())->toBe([0, 1, 2, 3]);
});

test('the seeder leaves an edited company home alone', function () {
    $this->seed(PortfolioSeeder::class);

    $section = LandingSection::query()
        ->whereNotNull('company_id')
        ->where('type', 'faq')
        ->sole();

    $section->update(['heading' => 'Hand written heading.', 'is_visible' => false]);

    $this->seed(PortfolioSeeder::class);

    $section->refresh();

    expect($section->heading)->toBe('Hand written heading.')
        ->and($section->is_visible)->toBeFalse()
        ->and(LandingSection::query()->whereNotNull('company_id')->count())->toBe(4);
});

test('seeded company sections carry authored copy, not factory placeholders', function () {
    $this->seed(PortfolioSeeder::class);

    $sections = Company::current()->sections()->ordered()->get();

    // Every seeded block states its own subheading. Anything left over from the
    // factory would be faker filler published on the live home page.
    foreach ($sections as $section) {
        expect($section->subheading)->not->toBeNull()
            ->and($section->subheading)->not->toMatch('/^\\w+ \\w+ \\w+ \\w+$/');
    }
});

test('the public founder identity is not the admin login account', function () {
    $this->seed(PortfolioSeeder::class);

    $profile = Profile::current();
    $company = Company::current();

    expect($profile->name)->toBe(config('portfolio.public_identity.founder_name'))
        ->and($company->name)->toBe(config('portfolio.public_identity.founder_name'))
        ->and($profile->public_email)
        ->toBe(config('portfolio.public_identity.contact_email'))
        ->and($company->public_email)
        ->toBe(config('portfolio.public_identity.contact_email'))
        // The account you log in with must never surface on a rendered page.
        ->and($profile->name)->not->toBe(config('portfolio.admin.name'))
        ->and($profile->public_email)->not->toBe(config('portfolio.admin.email'));
});
