<?php

namespace App\Models\Concerns;

use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Social profile links for a model that owns them (the studio and the founder).
 */
trait HasSocialLinks
{
    /**
     * The social profile links, in display order (polymorphic one-to-many).
     *
     * @return MorphMany<SocialLink, $this>
     */
    public function socialLinks(): MorphMany
    {
        return $this->morphMany(SocialLink::class, 'owner')->orderBy('sort_order');
    }

    /**
     * Replace the links with the admin form's { platform: url } map.
     * Blank URLs remove the link.
     *
     * @param  array<string, string|null>  $links
     */
    public function syncSocialLinks(array $links): void
    {
        $keep = [];

        foreach (SocialLink::PLATFORMS as $position => $platform) {
            $url = trim((string) ($links[$platform] ?? ''));

            if ($url === '') {
                continue;
            }

            $this->socialLinks()->updateOrCreate(
                ['platform' => $platform],
                ['url' => $url, 'sort_order' => $position],
            );

            $keep[] = $platform;
        }

        $this->socialLinks()->whereNotIn('platform', $keep)->delete();
        $this->unsetRelation('socialLinks');
    }

    /**
     * The links as the { platform: url } map the admin form edits.
     * Expects `socialLinks` to be eager loaded.
     *
     * @return array<string, string>
     */
    public function socialLinkMap(): array
    {
        return $this->socialLinks->pluck('url', 'platform')->all();
    }
}
