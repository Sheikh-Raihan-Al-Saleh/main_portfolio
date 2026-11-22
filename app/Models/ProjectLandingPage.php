<?php

namespace App\Models;

use App\Services\Media\MediaService;
use Database\Factories\ProjectLandingPageFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * A project's marketing case-study page (/p/{slug}).
 *
 * @property int $id
 * @property int $project_id
 * @property string|null $eyebrow
 * @property string|null $headline
 * @property string|null $subheadline
 * @property string|null $hero_video_url
 * @property string|null $primary_cta_label
 * @property string|null $primary_cta_url
 * @property string|null $secondary_cta_label
 * @property string|null $secondary_cta_url
 * @property string|null $accent_from
 * @property string|null $accent_to
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $og_image_path
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $og_image_url
 * @property-read Project $project
 * @property-read Collection<int, LandingPageHeroMedia> $heroMedia
 * @property-read Collection<int, LandingSection> $sections
 */
#[Fillable([
    'eyebrow', 'headline', 'subheadline', 'hero_video_url',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
    'accent_from', 'accent_to', 'seo_title', 'seo_description', 'og_image_path',
    'is_published',
])]
#[Appends(['og_image_url'])]
class ProjectLandingPage extends Model
{
    /** @use HasFactory<ProjectLandingPageFactory> */
    use HasFactory;

    /** Hero screenshot slots, by position. */
    public const HERO_SLOTS = [0 => 'desktop', 1 => 'tablet', 2 => 'phone'];

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<LandingPageHeroMedia, $this> */
    public function heroMedia(): HasMany
    {
        return $this->hasMany(LandingPageHeroMedia::class, 'landing_page_id')->orderBy('slot');
    }

    /** @return MorphMany<LandingSection, $this> */
    public function sections(): MorphMany
    {
        return $this->morphMany(LandingSection::class, 'sectionable');
    }

    protected function getOgImageUrlAttribute(): ?string
    {
        return MediaService::url($this->og_image_path);
    }

    /** @param  Builder<ProjectLandingPage>  $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
