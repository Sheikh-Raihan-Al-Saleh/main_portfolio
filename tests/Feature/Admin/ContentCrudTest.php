<?php

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Education;
use App\Models\Experience;
use App\Models\LandingSection;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('an admin can create a skill', function () {
    $this->actingAs($this->admin)
        ->post('/admin/skills', [
            'name' => 'TypeScript',
            'category' => 'language',
            'proficiency' => 90,
        ])
        ->assertRedirect('/admin/skills');

    expect(Skill::sole()->name)->toBe('TypeScript');
});

test('a skill category must be one of the known values', function () {
    $this->actingAs($this->admin)
        ->post('/admin/skills', [
            'name' => 'TypeScript',
            'category' => 'not-a-category',
            'proficiency' => 90,
        ])
        ->assertSessionHasErrors('category');
});

test('proficiency is capped at 100', function () {
    $this->actingAs($this->admin)
        ->post('/admin/skills', [
            'name' => 'TypeScript',
            'category' => 'language',
            'proficiency' => 140,
        ])
        ->assertSessionHasErrors('proficiency');
});

test('an admin can create an experience with highlights', function () {
    $this->actingAs($this->admin)
        ->post('/admin/experiences', [
            'company' => 'Nexus Software',
            'role' => 'Senior Engineer',
            'start_date' => '2023-01-01',
            'highlights' => ['Cut API latency by 62%.'],
        ])
        ->assertRedirect('/admin/experiences');

    $experience = Experience::sole();

    expect($experience->highlights)->toBe(['Cut API latency by 62%.'])
        ->and($experience->isCurrent())->toBeTrue();
});

test('an experience end date cannot precede its start date', function () {
    $this->actingAs($this->admin)
        ->post('/admin/experiences', [
            'company' => 'Nexus',
            'role' => 'Engineer',
            'start_date' => '2024-01-01',
            'end_date' => '2023-01-01',
        ])
        ->assertSessionHasErrors('end_date');
});

test('an admin can create an education entry', function () {
    $this->actingAs($this->admin)
        ->post('/admin/educations', [
            'institution' => 'University of Dhaka',
            'degree' => 'BSc',
            'field_of_study' => 'Computer Science',
            'start_date' => '2016-01-01',
            'end_date' => '2020-01-01',
        ])
        ->assertRedirect('/admin/educations');

    expect(Education::sole()->institution)->toBe('University of Dhaka');
});

test('an admin can update the site profile', function () {
    $this->actingAs($this->admin)
        ->post('/admin/profile', [
            'name' => 'Ada Lovelace',
            'headline' => 'Software Engineer',
            'public_email' => 'ada@example.com',
            'available_for_work' => true,
            'roles' => ['Engineer', 'Architect'],
            'socials' => ['github' => 'https://github.com/ada'],
        ])
        ->assertRedirect();

    $profile = Profile::current();

    expect($profile->name)->toBe('Ada Lovelace')
        ->and($profile->roles)->toBe(['Engineer', 'Architect'])
        ->and($profile->socials['github'])->toBe('https://github.com/ada')
        ->and($profile->available_for_work)->toBeTrue();

    // The singleton must never be duplicated.
    expect(Profile::count())->toBe(1);
});

test('a malformed social URL is rejected', function () {
    $this->actingAs($this->admin)
        ->post('/admin/profile', [
            'name' => 'Ada',
            'socials' => ['github' => 'not a url'],
        ])
        ->assertSessionHasErrors('socials.github');
});

test('an admin can store the founder message', function () {
    $this->actingAs($this->admin)
        ->post('/admin/profile', [
            'name' => 'Ada Lovelace',
            'founder_message' => "I started the studio because\n\ngood software deserves better.",
        ])
        ->assertRedirect();

    expect(Profile::current()->founder_message)
        ->toBe("I started the studio because\n\ngood software deserves better.");
});

test('an admin can update the company', function () {
    $this->actingAs($this->admin)
        ->post('/admin/company', [
            'name' => 'Sheikh Nabil',
            'headline' => 'Software studio',
            'tagline' => 'We build the systems our clients depend on.',
            'mission' => 'Software should outlive the project that paid for it.',
            'founded_year' => '2019',
            'hero_title' => '{{name}} builds software that ships.',
            'primary_cta_label' => 'Start a project',
            'primary_cta_url' => '#contact',
            'accepting_projects' => true,
            'public_email' => 'hello@example.com',
            'socials' => ['github' => 'https://github.com/org'],
        ])
        ->assertRedirect();

    $company = Company::current();

    expect($company->name)->toBe('Sheikh Nabil')
        ->and($company->mission)->toBe('Software should outlive the project that paid for it.')
        ->and($company->accepting_projects)->toBeTrue()
        ->and($company->socials['github'])->toBe('https://github.com/org');

    // The singleton must never be duplicated.
    expect(Company::count())->toBe(1);
});

test('a company founding year must look like a year', function () {
    $this->actingAs($this->admin)
        ->post('/admin/company', [
            'name' => 'Sheikh Nabil',
            'founded_year' => 'last spring',
        ])
        ->assertSessionHasErrors('founded_year');
});

test('the company edit page loads its blocks and section types', function () {
    $company = Company::current();
    $section = LandingSection::factory()->onCompany($company)->create();

    $this->actingAs($this->admin)
        ->get('/admin/company')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/Company')
            ->where('company.id', $company->id)
            ->has('sectionTypes')
            ->has('sections', 1)
            ->where('sections.0.id', $section->id),
        );
});

test('opening a message marks it read and the unread count drops', function () {
    $message = ContactMessage::factory()->create(['read_at' => null]);

    $this->actingAs($this->admin)
        ->get('/admin/messages')
        ->assertInertia(fn ($page) => $page->where('unreadMessages', 1));

    $this->actingAs($this->admin)->get("/admin/messages/{$message->id}")->assertOk();

    expect($message->fresh()->read_at)->not->toBeNull();

    $this->actingAs($this->admin)
        ->get('/admin/messages')
        ->assertInertia(fn ($page) => $page->where('unreadMessages', 0));
});

test('a message can be marked unread again', function () {
    $message = ContactMessage::factory()->read()->create();

    $this->actingAs($this->admin)
        ->patch("/admin/messages/{$message->id}/unread")
        ->assertRedirect('/admin/messages');

    expect($message->fresh()->read_at)->toBeNull();
});

test('an admin can delete a message', function () {
    $message = ContactMessage::factory()->create();

    $this->actingAs($this->admin)
        ->delete("/admin/messages/{$message->id}")
        ->assertRedirect('/admin/messages');

    expect(ContactMessage::count())->toBe(0);
});
