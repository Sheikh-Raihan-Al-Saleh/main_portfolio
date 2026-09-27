<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The company whose portfolio the home route presents. This table always holds
 * exactly one row -- use Company::current() rather than querying it directly.
 *
 * Distinct from Profile, which is the human behind the company: the About route
 * renders that one, the home route renders this.
 *
 * @property int $id
 * @property string $name
 * @property string|null $legal_name
 * @property string|null $headline
 * @property string|null $tagline
 * @property string|null $bio
 * @property string|null $mission
 * @property string|null $location
 * @property string|null $public_email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $founded_year
 * @property string|null $logo_path
 * @property string|null $og_image_path
 * @property string|null $hero_eyebrow
 * @property string|null $hero_title
 * @property string|null $hero_statement
 * @property string|null $primary_cta_label
 * @property string|null $primary_cta_url
 * @property string|null $secondary_cta_label
 * @property string|null $secondary_cta_url
 * @property string|null $status_text
 * @property bool $accepting_projects
 * @property array<string, string|null>|null $socials
 * @property array<string, mixed>|null $footer
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property-read string|null $logo_url
 * @property-read string|null $og_image_url
 */
#[Fillable([
    'name', 'legal_name', 'headline', 'tagline', 'bio', 'mission', 'location',
    'public_email', 'phone', 'website', 'founded_year', 'logo_path', 'og_image_path',
    'hero_eyebrow', 'hero_title', 'hero_statement', 'primary_cta_label',
    'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url', 'status_text',
    'accepting_projects', 'socials', 'footer', 'meta_title', 'meta_description',
])]
#[Appends(['logo_url', 'og_image_url'])]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, ResolvesMediaUrls;

    /**
     * Get the single company row, creating it on first access so the public
     * home page and the admin form never have to handle a missing company.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => 'Your Company',
            ...static::heroDefaults(),
            'footer' => static::footerDefaults(),
        ]);
    }

    /**
     * The order the hero columns were added in, so a row created before a
     * later migration still lands with every column populated.
     *
     * @return array<string, string>
     */
    public static function heroDefaults(): array
    {
        return [
            'hero_eyebrow' => 'Product studio',
            'hero_title' => '{{name}} builds software that ships.',
            'hero_statement' => 'Web apps, platforms and systems for growing teams.',
            'primary_cta_label' => 'Start a project',
            'primary_cta_url' => '#contact',
            'secondary_cta_label' => 'See our work',
            'secondary_cta_url' => '#work',
        ];
    }

    /**
     * Starting point for the company footer. Mirrors Profile::footerDefaults()
     * so the same editor component and the same {{placeholder}} tokens work for
     * both sites; the differences are the default link targets.
     *
     * @return array<string, mixed>
     */
    public static function footerDefaults(): array
    {
        return [
            'status_text' => 'Accepting new projects',
            'status_text_unavailable' => 'Currently unavailable',
            'copyright' => '© {{year}} {{name}}. All rights reserved.',
            'back_to_top' => 'Back to top',
            'columns' => [
                [
                    'title' => 'Quick Links',
                    'links' => [
                        ['label' => 'Home', 'url' => '/'],
                        ['label' => 'Work', 'url' => '/projects'],
                        ['label' => 'About us', 'url' => '/about'],
                        ['label' => 'Contact', 'url' => '#contact'],
                    ],
                ],
                [
                    'title' => 'Services',
                    'links' => [
                        ['label' => 'Web Development', 'url' => '#work'],
                        ['label' => 'API & Integrations', 'url' => '#work'],
                        ['label' => 'UI/UX Implementation', 'url' => '#work'],
                        ['label' => 'Performance Audits', 'url' => '#work'],
                    ],
                ],
                [
                    'title' => 'Resources',
                    'links' => [
                        ['label' => 'Case studies', 'url' => '/projects'],
                        ['label' => 'Founder portfolio', 'url' => '/founder'],
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
     * The home page body, ordered exactly as the sections are rendered.
     *
     * @return HasMany<LandingSection, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(LandingSection::class, 'company_id');
    }

    /**
     * Clients and partners, used for the logo strip and the About page.
     *
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'company_id');
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->logo_path);
    }

    protected function getOgImageUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->og_image_path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'accepting_projects' => 'boolean',
            'socials' => 'array',
            'footer' => 'array',
        ];
    }
}
