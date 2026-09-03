<?php

namespace App\Models;

use Database\Factories\EducationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $institution
 * @property string $degree
 * @property string|null $field_of_study
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string|null $grade
 * @property string|null $description
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
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
        ];
    }
}
