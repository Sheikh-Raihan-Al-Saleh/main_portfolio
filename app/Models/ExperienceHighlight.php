<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One bullet point (achievement) of an experience.
 *
 * @property int $id
 * @property int $experience_id
 * @property string $body
 * @property int $sort_order
 */
#[Fillable(['body', 'sort_order'])]
class ExperienceHighlight extends Model
{
    /** @return BelongsTo<Experience, $this> */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
