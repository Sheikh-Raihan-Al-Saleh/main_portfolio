<?php

use App\Models\Founder;
use App\Models\Project;
use App\Models\Studio;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Support\Facades\Storage;

test('the seeder builds a complete site and is safe to run twice', function () {
    Storage::fake('uploads');

    $this->seed(PortfolioSeeder::class);
    $this->seed(PortfolioSeeder::class);

    $studio = Studio::query()->sole();
    $founder = Founder::query()->sole();

    expect($studio->name)->not->toBe('Your Studio')
        ->and($studio->logo_path)->not->toBeNull()
        ->and($studio->clients()->count())->toBe(5)
        ->and($founder->studio_id)->toBe($studio->id)
        ->and($founder->skills()->count())->toBeGreaterThan(10)
        ->and(Project::query()->count())->toBe(6)
        ->and(Project::query()->where('owner_type', 'founder')->count())->toBe(3)
        ->and(Project::query()->has('landingPage')->count())->toBe(1);

    Storage::disk('uploads')->assertExists([$studio->logo_path, $studio->logo_mark_path]);

    // The seeded site renders.
    $this->get('/')->assertOk();
    $this->get('/founder')->assertOk();
});
