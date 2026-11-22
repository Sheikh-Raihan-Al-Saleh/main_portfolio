<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * A social profile link owned by a studio or a founder.
 *
 * @property int $id
 * @property string $owner_type
 * @property int $owner_id
 * @property string $platform
 * @property string|null $label
 * @property string $url
 * @property int $sort_order
 */
#[Fillable(['platform', 'label', 'url', 'sort_order'])]
class SocialLink extends Model
{
    /**
     * The platforms the admin form offers, in display order.
     */
    public const PLATFORMS = ['github', 'linkedin', 'x', 'dribbble', 'youtube', 'whatsapp', 'website'];

    /** @return MorphTo<Model, $this> */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
