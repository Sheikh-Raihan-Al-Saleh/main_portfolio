<?php

namespace App\Models;

use App\Services\Media\MediaService;
use App\Support\Defaults\FounderDefaults;
use Database\Factories\FounderFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * The person behind the studio (the /founder portfolio).
 *
 * Read it through FounderService::current(), never by querying directly.
 *
 * @property int $id
 * @property int|null $studio_id
 * @property string $name
 * @property string $slug
 * @property string|null $headline
 * @property string|null $tagline
 * @property string|null $bio
 * @property string|null $founder_message
 * @property array<int, string>|null $roles
 * @property bool $is_available_for_work
 * @property string|null $location
 * @property string|null $public_email
 * @property string|null $phone
 * @property string|null $avatar_path
 * @property string|null $logo_path
 * @property string|null $resume_path
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
 * @property-read string|null $avatar_url
 * @property-read string|null $logo_url
 * @property-read string|null $resume_url
 * @property-read string|null $og_image_url
 * @property-read array<string, mixed> $founder_content
 */
#[Fillable([
    'studio_id', 'name', 'slug', 'headline', 'tagline', 'bio', 'founder_message', 'roles',
    'is_available_for_work', 'location', 'public_email', 'phone',
    'avatar_path', 'logo_path', 'resume_path',
    'hero_title', 'hero_statement',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
    'footer', 'content', 'meta_title', 'meta_description', 'og_image_path',
])]
#[Appends(['avatar_url', 'logo_url', 'resume_url', 'og_image_url'])]
class Founder extends Model
{
    /** @use HasFactory<FounderFactory> */
    use HasFactory;

    /** @return BelongsTo<Studio, $this> */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /** @return MorphMany<Project, $this> */
    public function projects(): MorphMany
    {
        return $this->morphMany(Project::class, 'owner');
    }

    /** @return MorphMany<SocialLink, $this> */
    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'owner')->orderBy('sort_order');
    }

    /** @return MorphMany<ContactMessage, $this> */
    public function messages(): MorphMany
    {
        return $this->morphMany(ContactMessage::class, 'recipient');
    }

    /** @return HasMany<SkillCategory, $this> */
    public function skillCategories(): HasMany
    {
        return $this->hasMany(SkillCategory::class);
    }

    /** @return HasManyThrough<Skill, SkillCategory, $this> */
    public function skills(): HasManyThrough
    {
        return $this->hasManyThrough(Skill::class, SkillCategory::class);
    }

    /** @return HasMany<Experience, $this> */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /** @return HasMany<Education, $this> */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
    }

    /**
     * Page copy: stored edits merged over the defaults.
     *
     * @return array<string, mixed>
     */
    public function getFounderContentAttribute(): array
    {
        return FounderDefaults::mergeContent($this->content);
    }

    protected function getAvatarUrlAttribute(): ?string
    {
        return MediaService::url($this->avatar_path);
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return MediaService::url($this->logo_path);
    }

    protected function getResumeUrlAttribute(): ?string
    {
        return $this->resume_path ? route('resume', absolute: false) : null;
    }

    protected function getOgImageUrlAttribute(): ?string
    {
        return MediaService::url($this->og_image_path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'is_available_for_work' => 'boolean',
            'footer' => 'array',
            'content' => 'array',
        ];
    }
}
