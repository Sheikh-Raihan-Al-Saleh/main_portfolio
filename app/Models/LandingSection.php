<?php

namespace App\Models;

use App\Enums\LandingSectionType;
use Database\Factories\LandingSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * One composable block of a marketing page.
 *
 * A section hangs off exactly one owner: the company's home page, or a single
 * project's landing page. Both use the same block types and the same admin
 * editor; only the owner column differs.
 *
 * @property int $id
 * @property int|null $landing_page_id
 * @property int|null $company_id
 * @property LandingSectionType $type
 * @property string|null $eyebrow
 * @property string|null $heading
 * @property string|null $subheading
 * @property string|null $body
 * @property array<string, mixed>|null $data
 * @property int $sort_order
 * @property bool $is_visible
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ProjectLandingPage|null $landingPage
 * @property-read Company|null $company
 */
#[Fillable([
    'landing_page_id', 'company_id', 'type', 'eyebrow', 'heading', 'subheading',
    'body', 'data', 'sort_order', 'is_visible',
])]
class LandingSection extends Model
{
    /** @use HasFactory<LandingSectionFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        // The two owner columns are alternatives, so enforce that at the model
        // level too: a section with both or neither would be invisible to every
        // page and could never be reached from the admin either.
        static::saving(function (self $section): void {
            $owners = array_filter([
                $section->landing_page_id === null ? null : 'landing_page_id',
                $section->company_id === null ? null : 'company_id',
            ]);

            if (count($owners) !== 1) {
                throw new \InvalidArgumentException(
                    'A landing section must belong to exactly one of landing_page_id or company_id.',
                );
            }
        });
    }

    /** @return BelongsTo<ProjectLandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(ProjectLandingPage::class, 'landing_page_id');
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Every stored media path this section owns, flattened from the dot-paths
     * the type declares. Used to clean up uploads when the section is deleted.
     *
     * @return array<int, string>
     */
    public function mediaPaths(): array
    {
        $data = $this->data ?? [];
        $paths = [];

        foreach ($this->type->mediaPaths() as $pattern) {
            // A wildcard pattern yields a list; a plain key yields the scalar,
            // so normalise both to an array before collecting.
            foreach (Arr::wrap(data_get($data, $pattern)) as $value) {
                if (is_string($value) && $value !== '') {
                    $paths[] = $value;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    /** @param  Builder<LandingSection>  $query */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /** @param  Builder<LandingSection>  $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LandingSectionType::class,
            'data' => 'array',
            'is_visible' => 'boolean',
        ];
    }
}
