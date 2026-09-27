<?php

namespace Database\Factories;

use App\Enums\ProjectContext;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
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
            'title' => $title,
            'slug' => Str::slug($title),
            'context' => ProjectContext::Personal->value,
            'summary' => fake()->sentence(12),
            'description' => fake()->paragraphs(4, true),
            'tech_stack' => fake()->randomElements(
                ['Laravel', 'Vue', 'TypeScript', 'Tailwind CSS', 'MySQL', 'Redis', 'Docker', 'AWS'],
                4,
            ),
            'repo_url' => 'https://github.com/example/'.Str::slug($title),
            'live_url' => fake()->url(),
            'role' => 'Full-Stack Developer',
            'is_featured' => false,
            'is_published' => true,
            'sort_order' => 0,
            'started_at' => fake()->dateTimeBetween('-3 years', '-1 year'),
            'completed_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }

    /** Client work delivered by the studio, shown on the home route. */
    public function companyWork(): static
    {
        return $this->state(fn () => ['context' => ProjectContext::Company->value]);
    }

    /** The founder's own work, shown on the About route. */
    public function personalWork(): static
    {
        return $this->state(fn () => ['context' => ProjectContext::Personal->value]);
    }
}
