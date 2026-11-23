<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A client or partner of the studio.
 *
 * @property int $id
 * @property int $studio_id
 * @property string $name
 * @property string|null $logo_path
 * @property string|null $website_url
 * @property string|null $industry
 * @property string|null $summary
 * @property int $sort_order
 * @property bool $is_visible
 */
#[Fillable(['name', 'logo_path', 'website_url', 'industry', 'summary', 'sort_order', 'is_visible'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /**
     * The studio this client belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<Studio, $this>
     */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }
}
