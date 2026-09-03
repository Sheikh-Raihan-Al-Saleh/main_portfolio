<?php

namespace App\Enums;

/**
 * The section blocks a marketing landing page can be composed from.
 *
 * This enum is the single source of truth for the type list: it owns the human
 * label, the validation rules for the section's `data` payload, and which keys
 * inside that payload hold uploaded media that must be cleaned up on delete.
 * Keeping all four together stops the list drifting between the form request,
 * the controller and the Vue section registry.
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
     * Validation rules for this type's `data` payload, keyed relative to the
     * request root (i.e. already prefixed with `data.`).
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
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
     * Dot-paths inside `data` that hold stored media paths. `*` marks a list.
     * Used to delete uploaded files when a section is removed.
     *
     * @return array<int, string>
     */
    public function mediaPaths(): array
    {
        return match ($this) {
            self::DemoVideo => ['video_path', 'poster_path'],
            self::DemoWalkthrough => ['steps.*.image_path'],
            self::Gallery => ['images.*'],
            self::Testimonials => ['items.*.avatar_path'],
            default => [],
        };
    }

    /**
     * Every case as `{ value, label }`, for populating the admin's "add
     * section" menu without hardcoding the list in the frontend.
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
}
