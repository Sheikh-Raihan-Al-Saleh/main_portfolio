<?php

namespace App\Models;

use App\Models\Concerns\HasSocialLinks;
use App\Services\FileService;
use App\Support\ContentMerger;
use App\Support\Defaults\StudioDefaults;
use Database\Factories\StudioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The company (studio). Its name and logo brand the whole site.
 *
 * There is only one studio row: read it with Studio::current().
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
class Studio extends Model
{
    /** @use HasFactory<StudioFactory> */
    use HasFactory, HasSocialLinks;

    /** Cache key of the brand shared with every page. */
    private const BRAND_CACHE_KEY = 'studio.brand';

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The cached brand is cleared whenever the studio is saved, so a new name
     * or logo shows on the next page load. It waits for the transaction to
     * commit, so a request running meanwhile cannot cache the old brand again.
     */
    protected static function booted(): void
    {
        static::saved(fn () => DB::afterCommit(fn () => Cache::forget(self::BRAND_CACHE_KEY)));
    }

    /**
     * The studio with its social links. Created from the defaults on first
     * use, and loaded once per request.
     */
    public static function current(): self
    {
        return once(fn (): self => self::with('socialLinks')->orderBy('id')->first()
            ?? self::create(StudioDefaults::attributes())->load('socialLinks'));
    }

    /**
     * The company name and logos shown on every page. Cached until the
     * studio is saved again.
     *
     * @return array{name: string, short_name: string, tagline: string|null, logo_url: string|null, logo_dark_url: string|null, logo_mark_url: string|null, website_url: string|null}
     */
    public static function brand(): array
    {
        return Cache::rememberForever(self::BRAND_CACHE_KEY, function (): array {
            $studio = self::current();

            return [
                'name' => $studio->name,
                'short_name' => $studio->short_name ?: $studio->name,
                'tagline' => $studio->tagline,
                'logo_url' => FileService::url($studio->logo_path),
                'logo_dark_url' => FileService::url($studio->logo_dark_path),
                'logo_mark_url' => FileService::url($studio->logo_mark_path),
                'website_url' => $studio->website_url,
            ];
        });
    }

    /**
     * The home page copy: stored edits merged over the defaults, so a section
     * added later still renders completely.
     *
     * @return array<string, mixed>
     */
    public function pageContent(): array
    {
        return ContentMerger::merge(StudioDefaults::content(), $this->content);
    }

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
