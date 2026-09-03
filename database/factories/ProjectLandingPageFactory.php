<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectLandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectLandingPage>
 */
class ProjectLandingPageFactory extends Factory
{
    protected $model = ProjectLandingPage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'eyebrow' => 'Case study',
            'headline' => rtrim(fake()->sentence(5), '.'),
            'subheadline' => fake()->sentence(18),
            'hero_video_url' => null,
            'primary_cta_label' => 'See it live',
            'primary_cta_url' => fake()->url(),
            'secondary_cta_label' => 'Talk to me',
            'secondary_cta_url' => '/#contact',
            'accent_from' => '#6366f1',
            'accent_to' => '#a855f7',
            'seo_title' => null,
            'seo_description' => fake()->sentence(14),
            'is_published' => true,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['is_published' => false]);
    }
}
