<?php

namespace App\Enums;

/**
 * The content block types a page (the studio home page or a project landing
 * page) can be built from.
 *
 * Each type owns its label in the admin picker, the validation rules of its
 * `data` payload, and the `data` keys that hold uploaded files. The
 * `landing_sections.type` column stores the case value.
 */
enum LandingSectionType: string
{
    case Features = 'features';
    case DemoVideo = 'demo_video';
    case DemoEmbed = 'demo_embed';
    case DemoWalkthrough = 'demo_walkthrough';
    case Stats = 'stats';
    case Gallery = 'gallery';
    case Testimonials = 'testimonials';
    case Faq = 'faq';
    case Tech = 'tech';
    case Cta = 'cta';
    case RichText = 'richtext';

    /**
     * Every type as `{ value, label }`, for the admin "add block" menu.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type): array => ['value' => $type->value, 'label' => $type->label()],
            self::cases(),
        );
    }

    /**
     * The name shown in the admin block picker.
     */
    public function label(): string
    {
        return match ($this) {
            self::Features => 'Feature grid',
            self::DemoVideo => 'Video demo',
            self::DemoEmbed => 'Live embedded demo',
            self::DemoWalkthrough => 'Screenshot walkthrough',
            self::Stats => 'Stats',
            self::Gallery => 'Gallery',
            self::Testimonials => 'Testimonials',
            self::Faq => 'FAQ',
            self::Tech => 'Tech stack',
            self::Cta => 'Call to action',
            self::RichText => 'Rich text',
        };
    }

    /**
     * Validation rules of this type's `data`, keyed from the request root
     * (already prefixed with `data.`).
     *
     * @return array<string, array<int, string>>
     */
    public function dataRules(): array
    {
        return match ($this) {
            self::Features => [
                'data.items' => ['required', 'array', 'min:1', 'max:12'],
                'data.items.*.icon' => ['nullable', 'string', 'max:40'],
                'data.items.*.title' => ['required', 'string', 'max:120'],
                'data.items.*.text' => ['nullable', 'string', 'max:400'],
            ],
            self::DemoVideo => [
                'data.provider' => ['required', 'string', 'in:upload,youtube,vimeo'],
                'data.video_path' => ['nullable', 'string', 'max:2048'],
                'data.video_url' => ['nullable', 'string', 'url', 'max:2048'],
                'data.poster_path' => ['nullable', 'string', 'max:2048'],
                'data.caption' => ['nullable', 'string', 'max:300'],
                'data.autoplay' => ['nullable', 'boolean'],
            ],
            self::DemoEmbed => [
                // The https rule is enforced separately so the failure message
                // can explain why an http embed is rejected.
                'data.url' => ['required', 'string', 'url', 'max:2048'],
                'data.aspect' => ['required', 'string', 'in:16/9,4/3,mobile'],
                'data.chrome' => ['required', 'string', 'in:browser,device,none'],
                'data.allow_fullscreen' => ['nullable', 'boolean'],
            ],
            self::DemoWalkthrough => [
                'data.frame' => ['required', 'string', 'in:browser,laptop,phone'],
                'data.steps' => ['required', 'array', 'min:1', 'max:12'],
                'data.steps.*.image_path' => ['required', 'string', 'max:2048'],
                'data.steps.*.title' => ['required', 'string', 'max:120'],
                'data.steps.*.caption' => ['nullable', 'string', 'max:400'],
            ],
            self::Stats => [
                'data.items' => ['required', 'array', 'min:1', 'max:6'],
                'data.items.*.value' => ['required', 'numeric'],
                'data.items.*.suffix' => ['nullable', 'string', 'max:8'],
                'data.items.*.label' => ['required', 'string', 'max:80'],
            ],
            self::Gallery => [
                'data.images' => ['required', 'array', 'min:1', 'max:24'],
                'data.images.*' => ['required', 'string', 'max:2048'],
            ],
            self::Testimonials => [
                'data.items' => ['required', 'array', 'min:1', 'max:12'],
                'data.items.*.quote' => ['required', 'string', 'max:600'],
                'data.items.*.name' => ['required', 'string', 'max:120'],
                'data.items.*.role' => ['nullable', 'string', 'max:120'],
                'data.items.*.avatar_path' => ['nullable', 'string', 'max:2048'],
            ],
            self::Faq => [
                'data.items' => ['required', 'array', 'min:1', 'max:20'],
                'data.items.*.question' => ['required', 'string', 'max:240'],
                'data.items.*.answer' => ['required', 'string', 'max:2000'],
            ],
            self::Tech => [
                'data.items' => ['required', 'array', 'min:1', 'max:40'],
                'data.items.*' => ['required', 'string', 'max:60'],
            ],
            self::Cta => [
                'data.label' => ['required', 'string', 'max:80'],
                'data.url' => ['required', 'string', 'max:2048'],
                'data.note' => ['nullable', 'string', 'max:200'],
            ],
            self::RichText => [
                'data.markdown' => ['required', 'string', 'max:20000'],
            ],
        };
    }

    /**
     * Dot-paths inside `data` that hold uploaded files (`*` marks a list).
     *
     * @return array<int, string>
     */
    public function mediaKeys(): array
    {
        return match ($this) {
            self::DemoVideo => ['video_path', 'poster_path'],
            self::DemoWalkthrough => ['steps.*.image_path'],
            self::Gallery => ['images.*'],
            self::Testimonials => ['items.*.avatar_path'],
            default => [],
        };
    }
}
