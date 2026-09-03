<?php

namespace Database\Factories;

use App\Enums\LandingSectionType;
use App\Models\LandingSection;
use App\Models\ProjectLandingPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LandingSection>
 */
class LandingSectionFactory extends Factory
{
    protected $model = LandingSection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'landing_page_id' => ProjectLandingPage::factory(),
            'type' => LandingSectionType::Features,
            'eyebrow' => null,
            'heading' => rtrim(fake()->sentence(4), '.'),
            'subheading' => fake()->sentence(12),
            'body' => null,
            'data' => self::sampleData(LandingSectionType::Features),
            'sort_order' => 0,
            'is_visible' => true,
        ];
    }

    /**
     * Build this section as the given type, with a valid payload for it.
     */
    public function ofType(LandingSectionType $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
            'data' => self::sampleData($type),
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }

    /**
     * A payload that satisfies the type's own validation rules, so factories
     * and the seeder stay valid as those rules evolve.
     *
     * @return array<string, mixed>
     */
    public static function sampleData(LandingSectionType $type): array
    {
        return match ($type) {
            LandingSectionType::Features => [
                'items' => [
                    ['icon' => 'Zap', 'title' => 'Fast by default', 'text' => fake()->sentence(10)],
                    ['icon' => 'Shield', 'title' => 'Secure', 'text' => fake()->sentence(10)],
                    ['icon' => 'Layers', 'title' => 'Composable', 'text' => fake()->sentence(10)],
                ],
            ],
            LandingSectionType::DemoVideo => [
                'provider' => 'youtube',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'poster_path' => null,
                'caption' => fake()->sentence(8),
                'autoplay' => false,
            ],
            LandingSectionType::DemoEmbed => [
                'url' => 'https://example.com/demo',
                'aspect' => '16/9',
                'chrome' => 'browser',
                'allow_fullscreen' => true,
            ],
            LandingSectionType::DemoWalkthrough => [
                'frame' => 'browser',
                'steps' => [
                    ['image_path' => 'landing/steps/one.png', 'title' => 'Sign in', 'caption' => fake()->sentence(9)],
                    ['image_path' => 'landing/steps/two.png', 'title' => 'Configure', 'caption' => fake()->sentence(9)],
                    ['image_path' => 'landing/steps/three.png', 'title' => 'Ship', 'caption' => fake()->sentence(9)],
                ],
            ],
            LandingSectionType::Stats => [
                'items' => [
                    ['value' => 99.9, 'suffix' => '%', 'label' => 'Uptime'],
                    ['value' => 40, 'suffix' => 'ms', 'label' => 'Median response'],
                ],
            ],
            LandingSectionType::Gallery => [
                'images' => ['landing/gallery/one.png', 'landing/gallery/two.png'],
            ],
            LandingSectionType::Testimonials => [
                'items' => [
                    [
                        'quote' => fake()->sentence(18),
                        'name' => fake()->name(),
                        'role' => 'Product Lead',
                        'avatar_path' => null,
                    ],
                ],
            ],
            LandingSectionType::Faq => [
                'items' => [
                    ['question' => 'Is there a trial?', 'answer' => fake()->sentence(14)],
                    ['question' => 'Can I self-host?', 'answer' => fake()->sentence(14)],
                ],
            ],
            LandingSectionType::Tech => [
                'items' => ['Laravel', 'Vue', 'Tailwind CSS', 'MySQL'],
            ],
            LandingSectionType::Cta => [
                'label' => 'Start a project',
                'url' => '/#contact',
                'note' => 'Usually replies within a day.',
            ],
            LandingSectionType::RichText => [
                'markdown' => "## How it works\n\n".fake()->paragraph(4),
            ],
        };
    }
}
