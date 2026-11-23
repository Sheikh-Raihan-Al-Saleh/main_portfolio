<?php

namespace App\Models;

use Database\Factories\TechnologyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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

    /**
     * The projects that use this technology (many-to-many through the project_technology pivot).
     *
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withPivot('sort_order');
    }
}
