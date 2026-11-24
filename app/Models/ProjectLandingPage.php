<?php

namespace App\Models;

use Database\Factories\ProjectLandingPageFactory;
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
 * @property-read Project $project
 * @property-read Collection<int, LandingPageHeroMedia> $heroMedia
 * @property-read Collection<int, LandingSection> $sections
 */
class ProjectLandingPage extends Model
{
    /** @use HasFactory<ProjectLandingPageFactory> */
    use HasFactory;

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /** Hero screenshot slots, by position. */
    public const HERO_SLOTS = [0 => 'desktop', 1 => 'tablet', 2 => 'phone'];

    /**
     * Every file the page owns: the OG image, the hero screenshots and the
     * media of its blocks. Expects `heroMedia` and `sections` to be loaded.
     *
     * @return array<int, string>
     */
    public function filePaths(): array
    {
        return array_values(array_filter([
            $this->og_image_path,
            ...$this->heroMedia->pluck('path'),
            ...$this->sections->flatMap(fn (LandingSection $section): array => $section->mediaPaths()),
        ]));
    }

    /**
     * The project this landing page presents (inverse of one-to-one).
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The hero screenshots, ordered by slot (one-to-many).
     *
     * @return HasMany<LandingPageHeroMedia, $this>
     */
    public function heroMedia(): HasMany
    {
        return $this->hasMany(LandingPageHeroMedia::class, 'landing_page_id')->orderBy('slot');
    }

    /**
     * The composable content blocks of the page (polymorphic one-to-many).
     *
     * @return MorphMany<LandingSection, $this>
     */
    public function sections(): MorphMany
    {
        return $this->morphMany(LandingSection::class, 'sectionable');
    }

    /**
     * Only published landing pages.
     *
     * @param  Builder<self>  $query
     */
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
