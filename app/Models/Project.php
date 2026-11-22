<?php

namespace App\Models;

use App\Services\Media\MediaService;
use Carbon\CarbonInterface;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A piece of portfolio work, owned by the studio or the founder.
 *
 * @property int $id
 * @property string $owner_type
 * @property int $owner_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $description
 * @property string|null $cover_image_path
 * @property string|null $role
 * @property string|null $repo_url
 * @property string|null $live_url
 * @property bool $is_featured
 * @property bool $is_published
 * @property int $sort_order
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property CarbonInterface|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $cover_image_url
 * @property-read bool|null $has_landing_page Selected with withExists() by ProjectService.
 * @property-read Studio|Founder $owner
 * @property-read Collection<int, ProjectImage> $images
 * @property-read Collection<int, Technology> $technologies
 * @property-read ProjectLandingPage|null $landingPage
 */
#[Fillable([
    'title', 'slug', 'summary', 'description', 'cover_image_path', 'role',
    'repo_url', 'live_url', 'is_featured', 'is_published', 'sort_order',
    'started_at', 'completed_at', 'published_at',
])]
#[Appends(['cover_image_url'])]
#[RouteKey('slug')]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<ProjectImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /** @return BelongsToMany<Technology, $this> */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /** @return HasOne<ProjectLandingPage, $this> */
    public function landingPage(): HasOne
    {
        return $this->hasOne(ProjectLandingPage::class);
    }

    protected function getCoverImageUrlAttribute(): ?string
    {
        return MediaService::url($this->cover_image_path);
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

    /**
     * Work owned by one studio or founder.
     *
     * @param  Builder<Project>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, Model $owner): void
    {
        $query->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey());
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
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'started_at' => 'date',
            'completed_at' => 'date',
            'published_at' => 'datetime',
        ];
    }
}
