<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
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
#[Fillable([
    'institution', 'degree', 'field_of_study', 'start_date', 'end_date',
    'grade', 'description', 'sort_order',
])]
class Education extends Model
{
    /** @use HasFactory<EducationFactory> */
    use HasFactory;

    /** @return BelongsTo<Founder, $this> */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
    }

    /** @param  Builder<Education>  $query */
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
