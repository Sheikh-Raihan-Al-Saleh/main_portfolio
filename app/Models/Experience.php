<?php

namespace App\Models;

use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $company
 * @property string $role
 * @property string|null $employment_type
 * @property string|null $location
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property string|null $description
 * @property array<int, string>|null $highlights
 * @property string|null $company_url
 * @property string|null $logo_path
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string|null $logo_url
 */
#[Fillable([
    'company', 'role', 'employment_type', 'location', 'start_date', 'end_date',
    'description', 'highlights', 'company_url', 'logo_path', 'sort_order',
])]
#[Appends(['logo_url'])]
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    /**
     * A null end date means the role is ongoing.
     */
    public function isCurrent(): bool
    {
        return $this->end_date === null;
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return filled($this->logo_path)
            ? Storage::disk('public')->url($this->logo_path)
            : null;
    }

    /** @param  Builder<Experience>  $query */
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
            'highlights' => 'array',
        ];
    }
}
