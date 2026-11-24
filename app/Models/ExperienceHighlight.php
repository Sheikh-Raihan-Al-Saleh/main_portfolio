<?php

namespace App\Models;

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
class ExperienceHighlight extends Model
{
    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The role this bullet point belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Experience, $this>
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
