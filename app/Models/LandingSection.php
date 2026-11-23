<?php

namespace App\Models;

use App\Enums\LandingSectionType;
use Database\Factories\LandingSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
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
