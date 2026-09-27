<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'legal_name' => fake()->company().' Ltd',
            'headline' => 'Product studio',
            'tagline' => fake()->sentence(10),
            'bio' => fake()->paragraphs(3, true),
            'mission' => fake()->sentence(14),
            'location' => fake()->city(),
            'public_email' => fake()->unique()->companyEmail(),
            'phone' => '+8801'.fake()->numerify('#########'),
            'website' => 'https://'.fake()->domainName(),
            'founded_year' => (string) fake()->numberBetween(2012, 2024),
            'hero_eyebrow' => 'Product studio',
            'hero_title' => '{{name}} builds software that ships.',
            'hero_statement' => fake()->sentence(8),
            'accepting_projects' => true,
            'meta_title' => fake()->company().' — Software Studio',
            'meta_description' => fake()->sentence(20),
        ];
    }
}
