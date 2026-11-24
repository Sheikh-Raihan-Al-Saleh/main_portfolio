<?php

namespace App\Models;

use App\Services\FileService;
use Carbon\CarbonInterface;
use Database\Factories\ProjectFactory;
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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
 * @property-read bool|null $has_landing_page Selected with withExists() by the project queries.
 * @property-read Studio|Founder $owner
 * @property-read Collection<int, ProjectImage> $images
 * @property-read Collection<int, Technology> $technologies
 * @property-read ProjectLandingPage|null $landingPage
 */
#[RouteKey('slug')]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** Columns a project card needs (no long description). */
    public const CARD_COLUMNS = [
        'id', 'owner_type', 'owner_id', 'title', 'slug', 'summary', 'cover_image_path',
        'role', 'repo_url', 'live_url', 'is_featured', 'is_published', 'sort_order',
        'started_at', 'completed_at',
    ];

    /** Cache key of the sign-in page showcase. */
    private const SHOWCASE_CACHE_KEY = 'projects.login-showcase';

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The cached sign-in showcase is cleared whenever a project is saved or
     * deleted, once the transaction has committed.
     */
    protected static function booted(): void
    {
        static::saved(fn () => DB::afterCommit(fn () => self::forgetShowcase()));
        static::deleted(fn () => DB::afterCommit(fn () => self::forgetShowcase()));
    }

    /**
     * Published covers for the sign-in pages, in display order (cached for
     * 10 minutes).
     *
     * @return array<int, array{title: string, cover: string}>
     */
    public static function loginShowcase(): array
    {
        // query() is needed here: inside the model, self::published() would
        // call the protected scope method directly instead of the query scope.
        return Cache::remember(self::SHOWCASE_CACHE_KEY, now()->addMinutes(10), fn (): array => self::query()
            ->published()
            ->whereNotNull('cover_image_path')
            ->ordered()
            ->limit(6)
            ->get(['id', 'title', 'cover_image_path'])
            ->map(fn (self $project): array => ['title' => $project->title, 'cover' => (string) FileService::url($project->cover_image_path)])
            ->all());
    }

    /**
     * Forget the cached sign-in showcase.
     */
    public static function forgetShowcase(): void
    {
        Cache::forget(self::SHOWCASE_CACHE_KEY);
    }

    /**
     * Who the project belongs to: the studio or the founder (polymorphic inverse).
     *
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The gallery images, in display order (one-to-many).
     *
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The tech stack, in display order (many-to-many through the project_technology pivot).
     *
     * @return BelongsToMany<Technology, $this>
     */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class)
            ->withPivot('sort_order')
            ->orderByPivot('sort_order');
    }

    /**
     * The project's case-study landing page (one-to-one).
     *
     * @return HasOne<ProjectLandingPage, $this>
     */
    public function landingPage(): HasOne
    {
        return $this->hasOne(ProjectLandingPage::class);
    }

    /**
     * The landing page, only when it is published (one-to-one). Used to tell
     * the public pages whether to link to it.
     *
     * @return HasOne<ProjectLandingPage, $this>
     */
    public function publishedLandingPage(): HasOne
    {
        return $this->hasOne(ProjectLandingPage::class)->where('is_published', true);
    }

    /**
     * Only published projects.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Display order: by position, newest first when two share a position.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('id');
    }

    /**
     * Only the projects of one owner (the studio or the founder).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ownedBy(Builder $query, Studio|Founder $owner): void
    {
        $query->whereMorphedTo('owner', $owner);
    }

    /**
     * What a public project card needs: the card columns, the tech stack and
     * whether a published landing page exists (`has_landing_page`).
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function forCards(Builder $query): void
    {
        $query->select(self::CARD_COLUMNS)
            ->with('technologies:id,name,slug')
            ->withExists('publishedLandingPage as has_landing_page');
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
