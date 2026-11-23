<?php

namespace App\Models;

use Database\Factories\StudioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * The company (studio). Its name and logo brand the whole site.
 *
 * Read it through StudioService::current(), never by querying directly.
 *
 * @property int $id
 * @property string $name
 * @property string|null $short_name
 * @property string|null $legal_name
 * @property string $slug
 * @property string|null $logo_path
 * @property string|null $logo_dark_path
 * @property string|null $logo_mark_path
 * @property string|null $headline
 * @property string|null $tagline
 * @property string|null $bio
 * @property string|null $mission
 * @property int|null $founded_year
 * @property bool $is_accepting_projects
 * @property string|null $location
 * @property string|null $public_email
 * @property string|null $phone
 * @property string|null $website_url
 * @property string|null $hero_eyebrow
 * @property string|null $hero_title
 * @property string|null $hero_statement
 * @property string|null $primary_cta_label
 * @property string|null $primary_cta_url
 * @property string|null $secondary_cta_label
 * @property string|null $secondary_cta_url
 * @property array<string, mixed>|null $footer
 * @property array<string, mixed>|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'short_name', 'legal_name', 'slug', 'logo_path', 'logo_dark_path', 'logo_mark_path',
    'headline', 'tagline', 'bio', 'mission', 'founded_year', 'is_accepting_projects',
    'location', 'public_email', 'phone', 'website_url',
    'hero_eyebrow', 'hero_title', 'hero_statement',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
    'footer', 'content', 'meta_title', 'meta_description', 'og_image_path',
])]
class Studio extends Model
{
    /** @use HasFactory<StudioFactory> */
    use HasFactory;

    /**
     * The person who runs the studio (one-to-one).
     *
     * @return HasOne<Founder, $this>
     */
    public function founder(): HasOne
    {
        return $this->hasOne(Founder::class);
    }

    /**
     * The studio's clients and partners (one-to-many).
     *
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * The studio's client and product work (polymorphic one-to-many).
     *
     * @return MorphMany<Project, $this>
     */
    public function projects(): MorphMany
    {
        return $this->morphMany(Project::class, 'owner');
    }

    /**
     * The studio's social profile links, in display order (polymorphic one-to-many).
     *
     * @return MorphMany<SocialLink, $this>
     */
    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'owner')->orderBy('sort_order');
    }

    /**
     * The composable blocks of the studio home page.
     *
     * @return MorphMany<LandingSection, $this>
     */
    public function sections(): MorphMany
    {
        return $this->morphMany(LandingSection::class, 'sectionable');
    }

    /**
     * Enquiries sent from the studio contact form (polymorphic one-to-many).
     *
     * @return MorphMany<ContactMessage, $this>
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(ContactMessage::class, 'recipient');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
            'is_accepting_projects' => 'boolean',
            'footer' => 'array',
            'content' => 'array',
        ];
    }
}
