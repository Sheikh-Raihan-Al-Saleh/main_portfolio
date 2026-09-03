<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
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
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $project_id
 * @property string|null $eyebrow
 * @property string|null $headline
 * @property string|null $subheadline
 * @property string|null $hero_media_path
 * @property array<int, string>|null $hero_media_paths
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
 * @property-read string|null $hero_media_url
 * @property-read array<int, string> $hero_media_urls
 * @property-read string|null $og_image_url
 * @property-read Project|null $project
 * @property-read Collection<int, LandingSection> $sections
 */
#[Fillable([
    'eyebrow', 'headline', 'subheadline', 'hero_media_path', 'hero_media_paths', 'hero_video_url',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
    'accent_from', 'accent_to', 'seo_title', 'seo_description', 'og_image_path',
    'is_published',
])]
#[Appends(['hero_media_url', 'hero_media_urls', 'og_image_url'])]
class ProjectLandingPage extends Model
{
    /** @use HasFactory<ProjectLandingPageFactory> */
    use HasFactory;
    use ResolvesMediaUrls;

    /**
     * Keep the denormalised `projects.has_landing_page` flag true to the
     * relation, so factories, seeders and the admin cannot drift from it.
     */
    protected static function booted(): void
    {
        static::saved(function (self $landingPage): void {
            Project::query()
                ->whereKey($landingPage->project_id)
                ->update(['has_landing_page' => true]);
        });

        static::deleted(function (self $landingPage): void {
            Project::query()
                ->whereKey($landingPage->project_id)
                ->update(['has_landing_page' => false]);
        });
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<LandingSection, $this> */
    public function sections(): HasMany
    {
        return $this->hasMany(LandingSection::class, 'landing_page_id');
    }

    protected function getHeroMediaUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->hero_media_path);
    }

    /**
     * Get multiple hero media URLs from hero_media_paths array.
     * Falls back to single hero_media_path if hero_media_paths is empty.
     *
     * @return array<int, string>
     */
    protected function getHeroMediaUrlsAttribute(): array
    {
        $paths = $this->hero_media_paths ?? [];

        if (empty($paths) && $this->hero_media_path) {
            $paths = [$this->hero_media_path];
        }

        return array_filter(
            array_map(fn (string $path) => $this->mediaUrl($path), $paths),
            fn (?string $url): bool => $url !== null,
        );
    }

    protected function getOgImageUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->og_image_path);
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
            'hero_media_paths' => 'array',
        ];
    }
}
