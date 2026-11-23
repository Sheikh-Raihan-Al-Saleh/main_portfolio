<?php

namespace App\Models;

use Database\Factories\FounderFactory;
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
 */
#[Fillable([
    'studio_id', 'name', 'slug', 'headline', 'tagline', 'bio', 'founder_message', 'roles',
    'is_available_for_work', 'location', 'public_email', 'phone',
    'avatar_path', 'logo_path', 'resume_path',
    'hero_title', 'hero_statement',
    'primary_cta_label', 'primary_cta_url', 'secondary_cta_label', 'secondary_cta_url',
    'footer', 'content', 'meta_title', 'meta_description', 'og_image_path',
])]
class Founder extends Model
{
    /** @use HasFactory<FounderFactory> */
    use HasFactory;

    /**
     * The studio the founder runs (inverse of one-to-one).
     *
     * @return BelongsTo<Studio, $this>
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * The founder's personal projects (polymorphic one-to-many).
     *
     * @return MorphMany<Project, $this>
     */
    public function projects(): MorphMany
    {
        return $this->morphMany(Project::class, 'owner');
    }

    /**
     * The founder's social profile links, in display order (polymorphic one-to-many).
     *
     * @return MorphMany<SocialLink, $this>
     */
    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'owner')->orderBy('sort_order');
    }

    /**
     * Enquiries sent from the founder page contact form (polymorphic one-to-many).
     *
     * @return MorphMany<ContactMessage, $this>
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(ContactMessage::class, 'recipient');
    }

    /**
     * The founder's skill groups (one-to-many).
     *
     * @return HasMany<SkillCategory, $this>
     */
    public function skillCategories(): HasMany
    {
        return $this->hasMany(SkillCategory::class);
    }

    /**
     * Every skill of the founder, through the skill categories (has-many-through).
     *
     * @return HasManyThrough<Skill, SkillCategory, $this>
     */
    public function skills(): HasManyThrough
    {
        return $this->hasManyThrough(Skill::class, SkillCategory::class);
    }

    /**
     * The founder's work history (one-to-many).
     *
     * @return HasMany<Experience, $this>
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * The founder's education history (one-to-many).
     *
     * @return HasMany<Education, $this>
     */
    public function educations(): HasMany
    {
        return $this->hasMany(Education::class);
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
