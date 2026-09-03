<?php

namespace Database\Factories;

use App\Models\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    protected $model = Education::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution' => fake()->company().' University',
            'degree' => 'BSc',
            'field_of_study' => 'Computer Science',
            'start_date' => fake()->dateTimeBetween('-10 years', '-7 years'),
            'end_date' => fake()->dateTimeBetween('-7 years', '-3 years'),
            'grade' => '3.8 / 4.0',
            'description' => fake()->sentence(14),
            'sort_order' => 0,
        ];
    }
}
