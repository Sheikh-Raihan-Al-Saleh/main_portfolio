<?php

namespace Database\Factories;

use App\Models\Skill;
use App\Services\Founder\FounderService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Skills go into one of the current founder's categories by default.
 *
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'skill_category_id' => fn (): int => app(FounderService::class)->current()
                ->skillCategories()->orderBy('sort_order')->value('id'),
            'technology_id' => null,
            'name' => ucfirst(fake()->unique()->word()),
            'proficiency' => fake()->numberBetween(60, 98),
            'icon' => null,
            'is_featured' => false,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
