<?php

namespace App\Models;

use Database\Factories\SkillCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A group of the founder's skills ("Frontend", "DevOps & Cloud"...).
 *
 * @property int $id
 * @property int $founder_id
 * @property string $name
 * @property string $slug
 * @property int $sort_order
 * @property-read Collection<int, Skill> $skills
 */
#[Fillable(['name', 'slug', 'sort_order'])]
class SkillCategory extends Model
{
    /** @use HasFactory<SkillCategoryFactory> */
    use HasFactory;

    /**
     * Categories created for a new founder, in display order.
     */
    public const DEFAULTS = [
        'language' => 'Languages',
        'framework' => 'Frameworks',
        'frontend' => 'Frontend',
        'database' => 'Databases',
        'devops' => 'DevOps & Cloud',
        'tool' => 'Tooling',
    ];

    /** @return BelongsTo<Founder, $this> */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
    }

    /** @return HasMany<Skill, $this> */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
    }

    /** @param  Builder<SkillCategory>  $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }
}
