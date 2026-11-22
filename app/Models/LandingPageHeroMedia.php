<?php

namespace App\Models;

use App\Services\Media\MediaService;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One hero screenshot of a landing page, in a fixed slot
 * (see ProjectLandingPage::HERO_SLOTS).
 *
 * @property int $id
 * @property int $landing_page_id
 * @property int $slot
 * @property string $path
 * @property-read string|null $url
 */
#[Table('landing_page_hero_media')]
#[Fillable(['slot', 'path'])]
#[Appends(['url'])]
class LandingPageHeroMedia extends Model
{
    /** @return BelongsTo<ProjectLandingPage, $this> */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(ProjectLandingPage::class, 'landing_page_id');
    }

    protected function getUrlAttribute(): ?string
    {
        return MediaService::url($this->path);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'slot' => 'integer',
        ];
    }
}
