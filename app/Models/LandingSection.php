<?php

namespace App\Models;

use App\Enums\LandingSectionType;
use Database\Factories\LandingSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

/**
 * One composable block of a marketing page. The owner (sectionable) is the
 * studio home page or a project landing page.
 *
 * @property int $id
 * @property string $sectionable_type
 * @property int $sectionable_id
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
 */
#[Fillable(['type', 'eyebrow', 'heading', 'subheading', 'body', 'data', 'sort_order', 'is_visible'])]
class LandingSection extends Model
{
    /** @use HasFactory<LandingSectionFactory> */
    use HasFactory;

    /** @return MorphTo<Model, $this> */
    public function sectionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Every stored media path inside `data`, following the dot-paths the type
     * declares. Used to delete the files when the section goes away.
     *
     * @return array<int, string>
     */
    public function mediaPaths(): array
    {
        $paths = [];

        foreach ($this->type->mediaPaths() as $pattern) {
            foreach (Arr::wrap(data_get($this->data ?? [], $pattern)) as $value) {
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
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
