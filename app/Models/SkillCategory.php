<?php

namespace App\Models;

use Database\Factories\SkillCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
     * The founder this category belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Founder, $this>
     */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
    }

    /**
     * The skills in this category (one-to-many).
     *
     * @return HasMany<Skill, $this>
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class);
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
