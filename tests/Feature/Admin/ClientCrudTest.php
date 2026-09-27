<?php

use App\Models\Client;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
    $this->company = Company::current();
});

test('a non-admin cannot reach the client list', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/clients')
        ->assertForbidden();
});

test('a non-admin cannot create a client', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->post('/admin/clients', ['name' => 'Sneaky Co'])
        ->assertForbidden();
});

test('an admin can create a client with a logo', function () {
    Storage::fake('uploads');

    $this->actingAs($this->admin)
        ->post('/admin/clients', [
            'name' => 'Northwind',
            'industry' => 'Logistics',
            'summary' => 'Fleet tracking platform.',
            'website_url' => 'https://example.com',
            'logo' => UploadedFile::fake()->image('northwind.png'),
            'is_visible' => 1,
        ])
        ->assertRedirect();

    $client = Client::sole();

    expect($client->company_id)->toBe($this->company->id)
        ->and($client->name)->toBe('Northwind')
        ->and($client->is_visible)->toBeTrue()
        ->and($client->sort_order)->toBe(0)
        ->and($client->logo_path)->not->toBeNull();

    Storage::disk('uploads')->assertExists($client->logo_path);
});

test('an admin can upload an svg logo, as the field promises', function () {
    Storage::fake('uploads');

    $svg = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 32">
        <rect width="120" height="32" rx="6" fill="#0f172a"/>
        <text x="12" y="21" fill="#ffffff" font-family="sans-serif" font-size="14">Northwind</text>
    </svg>
    SVG;

    $this->actingAs($this->admin)
        ->post('/admin/clients', [
            'name' => 'Northwind',
            'logo' => UploadedFile::fake()->createWithContent('northwind.svg', $svg),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $client = Client::sole();

    expect($client->logo_path)->not->toBeNull();

    Storage::disk('uploads')->assertExists($client->logo_path);
});

test('an svg logo carrying script is refused', function () {
    Storage::fake('uploads');

    $svg = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 32">
        <script>alert(document.cookie)</script>
        <rect width="120" height="32" fill="#0f172a"/>
    </svg>
    SVG;

    $this->actingAs($this->admin)
        ->from('/admin/clients/create')
        ->post('/admin/clients', [
            'name' => 'Sketchy Co',
            'logo' => UploadedFile::fake()->createWithContent('logo.svg', $svg),
        ])
        ->assertRedirect('/admin/clients/create')
        ->assertSessionHasErrors('logo');

    expect(Client::count())->toBe(0);
});

test('a new client is appended to the end of the strip', function () {
    Client::factory()->create(['sort_order' => 0]);
    Client::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)
        ->post('/admin/clients', ['name' => 'Late Arrival'])
        ->assertRedirect();

    expect(Client::query()->max('sort_order'))->toBe(2);
});

test('the client list can be filtered by name', function () {
    Client::factory()->create(['name' => 'Northwind']);
    Client::factory()->create(['name' => 'Kitepay']);

    $this->actingAs($this->admin)
        ->get('/admin/clients?search=north')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/clients/Index')
            ->has('clients', 1)
            ->where('clients.0.name', 'Northwind')
            ->where('filters.search', 'north'),
        );
});

test('the list is returned in strip order', function () {
    Client::factory()->create(['name' => 'Third', 'sort_order' => 2]);
    Client::factory()->create(['name' => 'First', 'sort_order' => 0]);
    Client::factory()->create(['name' => 'Second', 'sort_order' => 1]);

    $this->actingAs($this->admin)
        ->get('/admin/clients')
        ->assertInertia(fn ($page) => $page
            ->where('clients.0.name', 'First')
            ->where('clients.1.name', 'Second')
            ->where('clients.2.name', 'Third'),
        );
});

test('an admin can update a client and replace its logo', function () {
    Storage::fake('uploads');

    $client = Client::factory()->create([
        'name' => 'Old Name',
        'logo_path' => UploadedFile::fake()->image('old.png')->store('clients', 'uploads'),
        'is_visible' => true,
    ]);

    $original = $client->logo_path;

    $this->actingAs($this->admin)
        ->put("/admin/clients/{$client->id}", [
            'name' => 'New Name',
            'logo' => UploadedFile::fake()->image('new.png'),
            'is_visible' => 0,
        ])
        ->assertRedirect();

    $client->refresh();

    expect($client->name)->toBe('New Name')
        ->and($client->is_visible)->toBeFalse()
        ->and($client->logo_path)->not->toBe($original)
        ->and($client->logo_path)->not->toBeNull();

    Storage::disk('uploads')->assertMissing($original);
    Storage::disk('uploads')->assertExists($client->logo_path);
});

test('an admin can clear a client logo', function () {
    Storage::fake('uploads');

    $client = Client::factory()->create([
        'logo_path' => UploadedFile::fake()->image('old.png')->store('clients', 'uploads'),
    ]);

    $original = $client->logo_path;

    $this->actingAs($this->admin)
        ->put("/admin/clients/{$client->id}", [
            'name' => $client->name,
            'remove_logo' => 1,
        ])
        ->assertRedirect();

    $client->refresh();

    expect($client->logo_path)->toBeNull();

    Storage::disk('uploads')->assertMissing($original);
});

test('an admin can reorder the strip', function () {
    $first = Client::factory()->create(['sort_order' => 0]);
    $second = Client::factory()->create(['sort_order' => 1]);
    $third = Client::factory()->create(['sort_order' => 2]);

    $this->actingAs($this->admin)
        ->post('/admin/clients/reorder', [
            'ids' => [$third->id, $first->id, $second->id],
        ])
        ->assertRedirect();

    expect($third->refresh()->sort_order)->toBe(0)
        ->and($first->refresh()->sort_order)->toBe(1)
        ->and($second->refresh()->sort_order)->toBe(2);
});

test('reordering rejects unknown client ids', function () {
    $client = Client::factory()->create();

    $this->actingAs($this->admin)
        ->post('/admin/clients/reorder', ['ids' => [$client->id, 9999]])
        ->assertSessionHasErrors('ids.1');
});

test('an admin can delete a client and its logo', function () {
    Storage::fake('uploads');

    $client = Client::factory()->create([
        'logo_path' => UploadedFile::fake()->image('logo.png')->store('clients', 'uploads'),
    ]);

    $path = $client->logo_path;

    $this->actingAs($this->admin)
        ->delete("/admin/clients/{$client->id}")
        ->assertRedirect();

    expect(Client::query()->count())->toBe(0);

    Storage::disk('uploads')->assertMissing($path);
});

test('the client name is required', function () {
    $this->actingAs($this->admin)
        ->post('/admin/clients', ['name' => ''])
        ->assertSessionHasErrors('name');
});

test('the client website must be a url', function () {
    $this->actingAs($this->admin)
        ->post('/admin/clients', [
            'name' => 'Northwind',
            'website_url' => 'not-a-url',
        ])
        ->assertSessionHasErrors('website_url');
});

test('clients belong to the company singleton', function () {
    $client = Client::factory()->create();

    expect($client->company->is($this->company))->toBeTrue();
});
