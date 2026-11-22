<?php

namespace Database\Factories;

use App\Models\Client;
use App\Services\Studio\StudioService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Clients belong to the current studio, so factory-made clients appear in the
 * real logo strip.
 *
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
            'studio_id' => fn (): int => app(StudioService::class)->current()->id,
            'name' => fake()->unique()->company(),
            'logo_path' => null,
            'website_url' => 'https://'.fake()->domainName(),
            'industry' => fake()->randomElement(['Fintech', 'Logistics', 'Retail', 'Healthcare', 'Education', 'Real estate']),
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
