<?php

namespace App\Models;

use App\Services\MediaService;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
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
 * @property-read string|null $logo_url
 */
#[Fillable(['name', 'logo_path', 'website_url', 'industry', 'summary', 'sort_order', 'is_visible'])]
#[Appends(['logo_url'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    /** @return BelongsTo<Studio, $this> */
    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return MediaService::url($this->logo_path);
    }

    /** @param  Builder<Client>  $query */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /** @param  Builder<Client>  $query */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
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
