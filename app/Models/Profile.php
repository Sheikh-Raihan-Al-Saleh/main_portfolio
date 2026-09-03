<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * The site owner's profile. This table always holds exactly one row --
 * use Profile::current() rather than querying it directly.
 *
 * @property int $id
 * @property string $name
 * @property string|null $hero_title
 * @property string|null $hero_statement
 * @property string|null $headline
 * @property string|null $tagline
 * @property string|null $bio
 * @property string|null $location
 * @property string|null $public_email
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property string|null $resume_path
 * @property string|null $og_image_path
 * @property bool $available_for_work
 * @property array<string, string|null>|null $socials
 * @property array<int, string>|null $roles
 * @property array<string, mixed>|null $footer
 * @property array<string, mixed>|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $avatar_url
 * @property-read string|null $resume_url
 * @property-read string|null $og_image_url
 */
#[Fillable([
    'name', 'hero_title', 'hero_statement', 'headline', 'tagline', 'bio',
    'location', 'public_email', 'phone', 'avatar_path', 'resume_path',
    'og_image_path', 'available_for_work', 'socials', 'roles', 'footer',
    'content', 'meta_title', 'meta_description',
])]
#[Appends(['avatar_url', 'resume_url', 'og_image_url'])]
class Profile extends Model
{
    use ResolvesMediaUrls;

    protected function getAvatarUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->avatar_path);
    }

    protected function getResumeUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->resume_path);
    }

    protected function getOgImageUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->og_image_path);
    }

    /**
     * Get the single profile row, creating it on first access so that the
     * public site and admin forms never have to handle a missing profile.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => 'Your Name',
            ...static::heroDefaults(),
            'footer' => static::footerDefaults(),
            'content' => static::contentDefaults(),
        ]);
    }

    /**
     * Default hero copy shown to a fresh install. Every piece is overridable
     * from the admin panel; the title may reference the profile name with the
     * {{name}} placeholder so the first name can be highlighted in the UI.
     *
     * @return array<string, string>
     */
    public static function heroDefaults(): array
    {
        return [
            'hero_title' => "Hi, I'm {{name}}.",
            'hero_statement' => 'I build things for the web.',
        ];
    }

    /**
     * Sensible starting point for the footer. Everything except the year is
     * overridable from the admin panel; every field may reference profile data
     * with {{placeholder}} tokens that are resolved when the footer renders.
     *
     * @return array<string, mixed>
     */
    public static function footerDefaults(): array
    {
        return [
            'status_text' => 'Available for select projects',
            'status_text_unavailable' => 'Currently unavailable',
            'copyright' => '© {{year}} {{name}}. All rights reserved. Built with ♥ and Laravel.',
            'back_to_top' => 'Back to top',
            'columns' => [
                [
                    'title' => 'Quick Links',
                    'links' => [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Skills', 'url' => '#skills'],
                        ['label' => 'Work', 'url' => '#projects'],
                        ['label' => 'Experience', 'url' => '#experience'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
                [
                    'title' => 'Services',
                    'links' => [
                        ['label' => 'Web Development', 'url' => '#skills'],
                        ['label' => 'Frontend Engineering', 'url' => '#skills'],
                        ['label' => 'UI/UX Implementation', 'url' => '#skills'],
                        ['label' => 'API Integration', 'url' => '#skills'],
                        ['label' => 'Performance Optimization', 'url' => '#skills'],
                    ],
                ],
                [
                    'title' => 'Resources',
                    'links' => [
                        ['label' => 'Download Resume', 'url' => '/resume'],
                        ['label' => 'GitHub Profile', 'url' => '{{github}}'],
                        ['label' => 'LinkedIn', 'url' => '{{linkedin}}'],
                    ],
                ],
                [
                    'title' => 'Contact',
                    'links' => [
                        ['label' => 'Email', 'url' => 'mailto:{{email}}'],
                        ['label' => 'WhatsApp Chat', 'url' => '{{whatsapp}}'],
                        ['label' => '{{location}}', 'url' => ''],
                    ],
                ],
            ],
            'legal_links' => [
                ['label' => 'Privacy Policy', 'url' => ''],
                ['label' => 'Terms of Service', 'url' => ''],
                ['label' => 'Sitemap', 'url' => ''],
            ],
        ];
    }

    /**
     * Defaults for the site's page copy. Every string the public sections and
     * hero render is driven from here, so a fresh install that has not been
     * touched in the admin panel still reads like a finished product.
     *
     * @return array<string, mixed>
     */
    public static function contentDefaults(): array
    {
        return [
            'sections' => [
                'about' => [
                    'eyebrow' => 'About',
                    'title' => 'Engineer by craft, builder by nature',
                    'highlight' => 'craft',
                    'description' => 'A quick note on who I am and how I work.',
                ],
                'skills' => [
                    'eyebrow' => 'Skills',
                    'title' => 'A production-grade toolkit',
                    'highlight' => 'toolkit',
                    'description' => 'Select a module to see the tools I reach for on the job — and how comfortable I am with each one.',
                ],
                'projects' => [
                    'eyebrow' => 'Projects',
                    'title' => 'Work that ships',
                    'highlight' => 'ships',
                    'description' => "A selection of products and platforms I've built end to end.",
                ],
                'experience' => [
                    'eyebrow' => 'Experience',
                    'title' => "Where I've shipped",
                    'highlight' => 'shipped',
                    'description' => 'The roles, teams and products that shaped how I build.',
                ],
                'contact' => [
                    'eyebrow' => 'Contact',
                    'title' => "Let's build something",
                    'highlight' => 'build',
                    'description' => 'Have a project, a role, or just a question? My inbox is open — expect a reply within a few working days.',
                ],
            ],
            'hero' => [
                'primary_cta_label' => 'View my work →',
                'primary_cta_url' => '#projects',
                'secondary_cta_label' => 'Get in touch',
                'secondary_cta_url' => '#contact',
                'resume_label' => './resume.pdf',
                'years_label' => 'years',
                'projects_label' => 'projects',
                'skills_label' => 'skills',
                'scroll_label' => 'scroll',
            ],
            'about' => [
                'role_label' => 'role',
                'location_label' => 'location',
                'email_label' => 'email',
                'phone_label' => 'phone',
                'available_open' => 'Open to work — remote',
                'available_closed' => 'Selective availability',
            ],
            'contact' => [
                'toast_title' => 'Message sent',
                'toast_description' => "Thanks for reaching out! I'll get back to you soon.",
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'available_for_work' => 'boolean',
            'socials' => 'array',
            'roles' => 'array',
            'footer' => 'array',
            'content' => 'array',
        ];
    }
}
