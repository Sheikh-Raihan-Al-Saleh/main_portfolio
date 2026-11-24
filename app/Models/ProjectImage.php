<?php

namespace App\Models;

use Database\Factories\ProjectImageFactory;
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
 */
class ProjectImage extends Model
{
    /** @use HasFactory<ProjectImageFactory> */
    use HasFactory;

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The project this image belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
