<?php

namespace App\Models;

use App\Enums\LandingSectionType;
use Database\Factories\LandingSectionFactory;
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
 * @property string $type A LandingSectionType value.
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
class LandingSection extends Model
{
    /** @use HasFactory<LandingSectionFactory> */
    use HasFactory;

    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * Every stored file path inside the block's `data`, following the keys
     * its type declares. Used to delete the files when the block goes away.
     *
     * @return array<int, string>
     */
    public function mediaPaths(): array
    {
        $paths = [];

        foreach (LandingSectionType::tryFrom($this->type)?->mediaKeys() ?? [] as $key) {
            foreach (Arr::wrap(data_get($this->data ?? [], $key)) as $value) {
                if (is_string($value) && $value !== '') {
                    $paths[] = $value;
                }
            }
        }

        return array_values(array_unique($paths));
    }

    /**
     * The page this block belongs to: the studio home page or a project landing page (polymorphic inverse).
     *
     * @return MorphTo<Model, $this>
     */
    public function sectionable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Display order: by position, then oldest first.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Only the rows marked visible on the public site.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
