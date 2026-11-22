<?php

namespace App\Models;

use Database\Factories\TechnologyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * A technology in the shared vocabulary (Laravel, Vue, PostgreSQL...).
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $icon
 */
#[Fillable(['name', 'slug', 'icon'])]
class Technology extends Model
{
    /** @use HasFactory<TechnologyFactory> */
    use HasFactory;

    /** @return BelongsToMany<Project, $this> */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withPivot('sort_order');
    }

    /** @param  Builder<Technology>  $query */
    #[Scope]
    protected function alphabetical(Builder $query): void
    {
        $query->orderBy('name');
    }
}
