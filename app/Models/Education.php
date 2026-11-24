<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * An entry in the founder's education history.
 *
 * @property int $id
 * @property int $founder_id
 * @property string $institution
 * @property string|null $degree
 * @property string|null $field_of_study
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property string|null $grade
 * @property string|null $description
 * @property int $sort_order
 */
#[Table('educations')]
class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The founder this education entry belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Founder, $this>
     */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
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
