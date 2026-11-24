<?php

namespace App\Models;

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
class SocialLink extends Model
{
    /**
     * Every column is mass assignable; controllers pass only validated fields.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The platforms the studio and founder forms offer, in display order.
     */
    public const PLATFORMS = ['github', 'linkedin', 'x', 'dribbble', 'youtube', 'whatsapp', 'website'];

    /**
     * Who the link belongs to: the studio or the founder (polymorphic inverse).
     *
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
}
