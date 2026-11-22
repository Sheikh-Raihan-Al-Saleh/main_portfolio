<?php

namespace Database\Factories;

use App\Models\Studio;
use App\Support\Defaults\StudioDefaults;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Studio>
 */
class StudioFactory extends Factory
{
    protected $model = Studio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            ...StudioDefaults::attributes(),
            'name' => $name,
            'slug' => Str::slug($name),
            'headline' => 'Product studio',
            'tagline' => fake()->sentence(10),
            'bio' => fake()->paragraph(4),
            'mission' => fake()->sentence(16),
            'founded_year' => (int) fake()->year('-2 years'),
            'location' => fake()->city(),
            'public_email' => fake()->companyEmail(),
            'is_accepting_projects' => true,
        ];
    }
}
