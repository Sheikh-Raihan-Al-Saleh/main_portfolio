<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Clients hang off the company singleton, the same row the seeder
            // and the public pages read, so a factory-made client shows up in the
            // real logo strip rather than in a throwaway company.
            'company_id' => Company::current()->id,
            'name' => fake()->unique()->company(),
            'logo_path' => null,
            'website_url' => 'https://'.fake()->domainName(),
            'industry' => fake()->randomElement([
                'Fintech',
                'Logistics',
                'Retail',
                'Healthcare',
                'Education',
                'Real estate',
            ]),
            'summary' => fake()->sentence(10),
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }
}
