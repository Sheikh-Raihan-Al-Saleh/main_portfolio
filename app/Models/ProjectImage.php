<?php

namespace App\Models;

use App\Services\Media\MediaService;
use Database\Factories\ProjectImageFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One gallery image of a project.
 *
 * @property int $id
 * @property int $project_id
 * @property string $path
 * @property string|null $alt
 * @property string|null $caption
 * @property int $sort_order
 * @property-read string|null $url
 */
#[Fillable(['path', 'alt', 'caption', 'sort_order'])]
#[Appends(['url'])]
class ProjectImage extends Model
{
    /** @use HasFactory<ProjectImageFactory> */
    use HasFactory;

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    protected function getUrlAttribute(): ?string
    {
        return MediaService::url($this->path);
    }
}
