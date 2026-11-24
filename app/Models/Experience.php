<?php

namespace App\Models;

use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A role in the founder's work history. A null end_date means current.
 *
 * @property int $id
 * @property int $founder_id
 * @property string $company
 * @property string $role
 * @property string|null $employment_type
 * @property string|null $location
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string|null $description
 * @property string|null $company_url
 * @property string|null $logo_path
 * @property int $sort_order
 * @property-read Collection<int, ExperienceHighlight> $highlights
 */
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The founder this role belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Founder, $this>
     */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
    }

    /**
     * The bullet points of this role, in display order (one-to-many).
     *
     * @return HasMany<ExperienceHighlight, $this>
     */
    public function highlights(): HasMany
    {
        return $this->hasMany(ExperienceHighlight::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Display order: by position, then the latest start first.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderByDesc('start_date');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'sort_order' => 'integer',
        ];
    }
}
