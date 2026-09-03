<?php

namespace Database\Factories;

use App\Models\Experience;
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
            'company' => fake()->company(),
            'role' => fake()->jobTitle(),
            'employment_type' => 'Full-time',
            'location' => fake()->city().', Remote',
            'start_date' => fake()->dateTimeBetween('-6 years', '-2 years'),
            'end_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'description' => fake()->paragraph(),
            'highlights' => [fake()->sentence(), fake()->sentence()],
            'sort_order' => 0,
        ];
    }

    public function current(): static
    {
        return $this->state(fn () => ['end_date' => null]);
    }
}
