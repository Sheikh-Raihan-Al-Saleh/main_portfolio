<?php

namespace App\Models;

use App\Services\Media\MediaService;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
 * @property-read string|null $logo_url
 * @property-read bool $is_current
 * @property-read Collection<int, ExperienceHighlight> $highlights
 */
#[Fillable([
    'company', 'role', 'employment_type', 'location', 'start_date', 'end_date',
    'description', 'company_url', 'logo_path', 'sort_order',
])]
#[Appends(['logo_url', 'is_current'])]
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    /** @return BelongsTo<Founder, $this> */
    public function founder(): BelongsTo
    {
        return $this->belongsTo(Founder::class);
    }

    /** @return HasMany<ExperienceHighlight, $this> */
    public function highlights(): HasMany
    {
        return $this->hasMany(ExperienceHighlight::class)->orderBy('sort_order')->orderBy('id');
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return MediaService::url($this->logo_path);
    }

    protected function getIsCurrentAttribute(): bool
    {
        return $this->end_date === null;
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
            'sort_order' => 'integer',
        ];
    }
}
