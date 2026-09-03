<?php

namespace App\Models;

use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $cover_image_path
 * @property array<int, string>|null $gallery
 * @property array<int, string>|null $tech_stack
 * @property string|null $repo_url
 * @property string|null $live_url
 * @property string|null $role
 * @property bool $is_featured
 * @property bool $is_published
 * @property bool $has_landing_page
 * @property int $sort_order
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $cover_image_url
 * @property-read array<int, string> $gallery_urls
 * @property-read string|null $landing_url
 * @property-read ProjectLandingPage|null $landingPage
 */
#[Fillable([
    'title', 'slug', 'summary', 'description', 'cover_image_path', 'gallery',
    'tech_stack', 'repo_url', 'live_url', 'role', 'is_featured', 'is_published',
    'sort_order', 'started_at', 'completed_at',
])]
#[Appends(['cover_image_url', 'gallery_urls', 'landing_url'])]
#[RouteKey('slug')]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /**
     * Mirrors the column default so a project that has not been reloaded still
     * reports false rather than null.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'has_landing_page' => false,
    ];

    protected static function booted(): void
    {
        static::saving(function (self $project): void {
            if (blank($project->slug)) {
                $project->slug = self::uniqueSlug($project->title, $project->id);
            }
        });
    }

    /**
     * Build a URL-safe slug, appending a counter until it no longer collides.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'project';
        $slug = $base;
        $suffix = 2;

        while (self::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /** @return HasOne<ProjectLandingPage, $this> */
    public function landingPage(): HasOne
    {
        return $this->hasOne(ProjectLandingPage::class);
    }

    /**
     * Marketing landing page URL, or null when there is no published one.
     *
     * Relies on `has_landing_page` first so an unloaded relation does not
     * trigger a query for the overwhelming majority of projects that have no
     * case study at all.
     */
    protected function getLandingUrlAttribute(): ?string
    {
        if (! $this->has_landing_page) {
            return null;
        }

        return $this->landingPage?->is_published
            ? route('landing.show', $this->slug, absolute: false)
            : null;
    }

    protected function getCoverImageUrlAttribute(): ?string
    {
        return filled($this->cover_image_path)
            ? Storage::disk('public')->url($this->cover_image_path)
            : null;
    }

    /**
     * @return array<int, string>
     */
    protected function getGalleryUrlsAttribute(): array
    {
        return array_map(
            fn (string $path): string => Storage::disk('public')->url($path),
            $this->gallery ?? [],
        );
    }

    /** @param  Builder<Project>  $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /** @param  Builder<Project>  $query */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /** @param  Builder<Project>  $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'tech_stack' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'has_landing_page' => 'boolean',
            'started_at' => 'date',
            'completed_at' => 'date',
        ];
    }
}
