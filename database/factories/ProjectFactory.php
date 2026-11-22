<?php

namespace Database\Factories;

use App\Models\Project;
use App\Services\FounderService;
use App\Services\StudioService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Projects belong to the current studio by default, the same record the
 * public pages read, so a factory-made project shows up on the real site.
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(rtrim(fake()->unique()->sentence(3), '.'));

        return [
            'owner_type' => 'studio',
            'owner_id' => fn (): int => app(StudioService::class)->current()->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(4, true),
            'repo_url' => 'https://github.com/example/'.Str::slug($title),
            'live_url' => fake()->url(),
            'role' => 'Full-stack developer',
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => 0,
            'started_at' => fake()->dateTimeBetween('-3 years', '-1 year'),
            'completed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'published_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false, 'published_at' => null]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /** Studio work (client and company projects). */
    public function forStudio(): static
    {
        return $this->state(fn () => [
            'owner_type' => 'studio',
            'owner_id' => app(StudioService::class)->current()->id,
        ]);
    }

    /** The founder's personal work. */
    public function forFounder(): static
    {
        return $this->state(fn () => [
            'owner_type' => 'founder',
            'owner_id' => app(FounderService::class)->current()->id,
        ]);
    }
}
