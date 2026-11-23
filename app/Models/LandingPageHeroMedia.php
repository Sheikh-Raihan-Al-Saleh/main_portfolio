<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One hero screenshot of a landing page, in a fixed slot
 * (see LandingPageService::HERO_SLOTS).
 *
 * @property int $id
 * @property int $landing_page_id
 * @property int $slot
 * @property string $path
 */
#[Table('landing_page_hero_media')]
#[Fillable(['slot', 'path'])]
class LandingPageHeroMedia extends Model
{
    /**
     * The landing page this screenshot belongs to (inverse of one-to-many).
     *
     * @return BelongsTo<ProjectLandingPage, $this>
     */
    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(ProjectLandingPage::class, 'landing_page_id');
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
