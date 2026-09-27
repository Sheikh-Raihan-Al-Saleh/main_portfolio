<?php

namespace App\Models;

use App\Concerns\ResolvesMediaUrls;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A client or partner the studio has delivered for.
 *
 * These back the "trusted by" logo strip on the company home page. A client is
 * never removed when its project is, because the relationship outlives any one
 * engagement, and the logo stays useful as social proof elsewhere on the site.
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string|null $logo_path
 * @property string|null $website_url
 * @property string|null $industry
 * @property string|null $summary
 * @property int $sort_order
 * @property bool $is_visible
 * @property-read string|null $logo_url
 */
#[Fillable([
    'company_id', 'name', 'logo_path', 'website_url', 'industry', 'summary',
    'sort_order', 'is_visible',
])]
#[Appends(['logo_url'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, ResolvesMediaUrls;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @param  Builder<Client>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    /**
     * @param  Builder<Client>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    protected function getLogoUrlAttribute(): ?string
    {
        return $this->mediaUrl($this->logo_path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
        ];
    }
}
