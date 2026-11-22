<?php

namespace Database\Factories;

use App\Models\Founder;
use App\Services\Studio\StudioService;
use App\Support\Defaults\FounderDefaults;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Founder>
 */
class FounderFactory extends Factory
{
    protected $model = Founder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            ...FounderDefaults::attributes(),
            'studio_id' => fn (): int => app(StudioService::class)->current()->id,
            'name' => $name,
            'slug' => Str::slug($name),
            'headline' => 'Full-stack engineer',
            'tagline' => fake()->sentence(10),
            'bio' => fake()->paragraph(4),
            'roles' => ['Full-stack engineer', 'Product builder'],
            'location' => fake()->city(),
            'public_email' => fake()->safeEmail(),
            'is_available_for_work' => true,
        ];
    }
}
