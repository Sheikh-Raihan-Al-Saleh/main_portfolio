<?php

namespace App\Models;

use Database\Factories\SkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $category
 * @property int $proficiency
 * @property string|null $icon
 * @property bool $is_featured
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'category', 'proficiency', 'icon', 'is_featured', 'sort_order'])]
class Skill extends Model
{
    /** @use HasFactory<SkillFactory> */
    use HasFactory;

    /**
     * The categories a skill can be filed under, keyed by stored value.
     *
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            'language' => 'Language',
            'framework' => 'Framework',
            'frontend' => 'Frontend',
            'database' => 'Database',
            'devops' => 'DevOps & Cloud',
            'tool' => 'Tooling',
        ];
    }

    /** @param  Builder<Skill>  $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /** @param  Builder<Skill>  $query */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'proficiency' => 'integer',
            'is_featured' => 'boolean',
        ];
    }
}
