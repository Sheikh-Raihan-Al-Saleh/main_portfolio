<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One skill of the founder, with a proficiency from 0 to 100.
 *
 * @property int $id
 * @property int $skill_category_id
 * @property int|null $technology_id
 * @property string $name
 * @property int $proficiency
 * @property string|null $icon
 * @property bool $is_featured
 * @property int $sort_order
 * @property-read SkillCategory $category
 */
#[Fillable(['skill_category_id', 'technology_id', 'name', 'proficiency', 'icon', 'is_featured', 'sort_order'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The category this skill is grouped under (inverse of one-to-many).
     *
     * @return BelongsTo<SkillCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * The matching technology of the shared vocabulary, if any (inverse of one-to-many).
     *
     * @return BelongsTo<Technology, $this>
     */
    public function technology(): BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'proficiency' => 'integer',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
