<?php

use App\Enums\LandingSectionType;
use App\Models\Company;
use App\Models\LandingSection;
use App\Models\Project;
use App\Models\ProjectLandingPage;
use App\Models\User;
use Database\Factories\LandingSectionFactory;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->project = Project::factory()->create();
    $this->landingPage = ProjectLandingPage::factory()->for($this->project)->create();
});

test('a non-admin cannot create a section', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => 'features',
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertForbidden();
});

test('an admin can create a section of every type', function (LandingSectionType $type) {
    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => $type->value,
            'heading' => 'A heading',
            'data' => LandingSectionFactory::sampleData($type),
        ])
        ->assertRedirect();

    $section = LandingSection::sole();

    expect($section->type)->toBe($type)
        ->and($section->heading)->toBe('A heading')
        ->and($section->is_visible)->toBeFalse();
})->with(LandingSectionType::cases());

test('sections are appended to the end of the page', function () {
    LandingSection::factory()->for($this->landingPage, 'landingPage')->create([
        'sort_order' => 5,
    ]);

    $this->actingAs($this->admin)->post('/admin/landing-sections', [
        'landing_page_id' => $this->landingPage->id,
        'type' => 'faq',
        'is_visible' => true,
        'data' => LandingSectionFactory::sampleData(LandingSectionType::Faq),
    ]);

    expect(LandingSection::where('type', 'faq')->sole()->sort_order)->toBe(6);
});

test('a malformed payload is rejected for its type', function () {
    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => 'faq',
            // Missing the required `answer` key.
            'data' => ['items' => [['question' => 'Why?']]],
        ])
        ->assertSessionHasErrors('data.items.0.answer');
});

test('a demo embed must use https', function () {
    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => 'demo_embed',
            'data' => [
                'url' => 'http://example.com/demo',
                'aspect' => '16/9',
                'chrome' => 'browser',
            ],
        ])
        ->assertSessionHasErrors('data.url');
});

test('a demo embed host outside the allowlist is rejected', function () {
    config()->set('portfolio.embed_hosts', ['demo.example.com']);

    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => 'demo_embed',
            'data' => [
                'url' => 'https://evil.test/demo',
                'aspect' => '16/9',
                'chrome' => 'browser',
            ],
        ])
        ->assertSessionHasErrors('data.url');
});

test('a subdomain of an allowed embed host is accepted', function () {
    config()->set('portfolio.embed_hosts', ['example.com']);

    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'type' => 'demo_embed',
            'data' => [
                'url' => 'https://demo.example.com/app',
                'aspect' => '16/9',
                'chrome' => 'browser',
            ],
        ])
        ->assertSessionHasNoErrors();
});

test('an admin can update a section', function () {
    $section = LandingSection::factory()->for($this->landingPage, 'landingPage')->create();

    $this->actingAs($this->admin)
        ->put("/admin/landing-sections/{$section->id}", [
            'heading' => 'Updated heading',
            'is_visible' => false,
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertRedirect();

    expect($section->fresh()->heading)->toBe('Updated heading')
        ->and($section->fresh()->is_visible)->toBeFalse();
});

test('reordering persists the new sort order', function () {
    $first = LandingSection::factory()->for($this->landingPage, 'landingPage')->create();
    $second = LandingSection::factory()->for($this->landingPage, 'landingPage')->create();

    $this->actingAs($this->admin)
        ->post('/admin/landing-sections/reorder', ['ids' => [$second->id, $first->id]])
        ->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1);
});

test('deleting a section removes its uploaded media', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('landing/steps/one.png', 'x');
    Storage::disk('uploads')->put('landing/steps/two.png', 'x');

    $section = LandingSection::factory()
        ->for($this->landingPage, 'landingPage')
        ->ofType(LandingSectionType::DemoWalkthrough)
        ->create([
            'data' => [
                'frame' => 'browser',
                'steps' => [
                    ['image_path' => 'landing/steps/one.png', 'title' => 'One', 'caption' => null],
                    ['image_path' => 'landing/steps/two.png', 'title' => 'Two', 'caption' => null],
                ],
            ],
        ]);

    $this->actingAs($this->admin)
        ->delete("/admin/landing-sections/{$section->id}")
        ->assertRedirect();

    expect(LandingSection::find($section->id))->toBeNull();
    Storage::disk('uploads')->assertMissing('landing/steps/one.png');
    Storage::disk('uploads')->assertMissing('landing/steps/two.png');
});

test('updating a section deletes media it no longer references', function () {
    Storage::fake('uploads');
    Storage::disk('uploads')->put('landing/gallery/keep.png', 'x');
    Storage::disk('uploads')->put('landing/gallery/drop.png', 'x');

    $section = LandingSection::factory()
        ->for($this->landingPage, 'landingPage')
        ->ofType(LandingSectionType::Gallery)
        ->create([
            'data' => ['images' => ['landing/gallery/keep.png', 'landing/gallery/drop.png']],
        ]);

    $this->actingAs($this->admin)->put("/admin/landing-sections/{$section->id}", [
        'data' => ['images' => ['landing/gallery/keep.png']],
    ]);

    Storage::disk('uploads')->assertExists('landing/gallery/keep.png');
    Storage::disk('uploads')->assertMissing('landing/gallery/drop.png');
});

test('deleting a landing page cascades to its sections', function () {
    LandingSection::factory()->for($this->landingPage, 'landingPage')->count(3)->create();

    $this->landingPage->delete();

    expect(LandingSection::count())->toBe(0);
});

test('a section can belong to the company instead of a landing page', function () {
    $company = Company::current();

    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'company_id' => $company->id,
            'type' => 'features',
            'is_visible' => true,
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertRedirect();

    $section = LandingSection::sole();

    expect($section->company_id)->toBe($company->id)
        ->and($section->landing_page_id)->toBeNull()
        ->and($section->company->is($company))->toBeTrue();
});

test('a section must have exactly one owner', function () {
    $company = Company::current();

    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'landing_page_id' => $this->landingPage->id,
            'company_id' => $company->id,
            'type' => 'features',
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertSessionHasErrors(['landing_page_id', 'company_id']);

    expect(LandingSection::count())->toBe(0);
});

test('a section with no owner is rejected', function () {
    $this->actingAs($this->admin)
        ->post('/admin/landing-sections', [
            'type' => 'features',
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertSessionHasErrors(['landing_page_id', 'company_id']);
});

test('a section cannot be moved to another owner on update', function () {
    $section = LandingSection::factory()->for($this->landingPage, 'landingPage')->create();
    $company = Company::current();

    $this->actingAs($this->admin)
        ->put("/admin/landing-sections/{$section->id}", [
            'landing_page_id' => null,
            'company_id' => $company->id,
            'is_visible' => true,
            'data' => LandingSectionFactory::sampleData(LandingSectionType::Features),
        ])
        ->assertRedirect();

    $section->refresh();

    expect($section->landing_page_id)->toBe($this->landingPage->id)
        ->and($section->company_id)->toBeNull();
});

test('the company home page only shows visible company sections in order', function () {
    $company = Company::current();
    $other = Company::factory()->create();

    LandingSection::factory()->onCompany($company)->create([
        'sort_order' => 1,
        'heading' => 'Second',
        'is_visible' => true,
    ]);
    LandingSection::factory()->onCompany($company)->create([
        'sort_order' => 0,
        'heading' => 'First',
        'is_visible' => true,
    ]);
    LandingSection::factory()->onCompany($company)->create([
        'sort_order' => 2,
        'heading' => 'Hidden',
        'is_visible' => false,
    ]);
    LandingSection::factory()->onCompany($other)->create([
        'sort_order' => 3,
        'heading' => 'Other company',
        'is_visible' => true,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('public/Home')
            ->has('sections', 2)
            ->where('sections.0.heading', 'First')
            ->where('sections.1.heading', 'Second'),
        );
});
