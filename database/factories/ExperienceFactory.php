<?php

namespace Database\Factories;

use App\Models\Experience;
use App\Services\FounderService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    protected $model = Experience::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'founder_id' => fn (): int => app(FounderService::class)->current()->id,
            'company' => fake()->company(),
            'role' => fake()->jobTitle(),
            'employment_type' => 'Full-time',
            'location' => fake()->city().', Remote',
            'start_date' => fake()->dateTimeBetween('-6 years', '-2 years'),
            'end_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'description' => fake()->paragraph(),
            'company_url' => null,
            'logo_path' => null,
            'sort_order' => 0,
        ];
    }

    public function current(): static
    {
        return $this->state(fn () => ['end_date' => null]);
    }
}
