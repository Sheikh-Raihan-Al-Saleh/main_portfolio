<?php

namespace App\Services\SocialLink;

use App\Models\Founder;
use App\Models\SocialLink;
use App\Models\Studio;

/**
 * Keeps an owner's social_links rows in step with the admin form, which sends
 * a simple { platform: url } map.
 */
class SocialLinkService
{
    /**
     * Replace the owner's links with the given map. Blank URLs are removed.
     *
     * @param  array<string, string|null>  $links
     */
    public function sync(Studio|Founder $owner, array $links): void
    {
        $relation = $owner->socialLinks();

        $keep = [];

        foreach (SocialLink::PLATFORMS as $position => $platform) {
            $url = trim((string) ($links[$platform] ?? ''));

            if ($url === '') {
                continue;
            }

            $relation->updateOrCreate(
                ['platform' => $platform],
                ['url' => $url, 'sort_order' => $position],
            );

            $keep[] = $platform;
        }

        $owner->socialLinks()->whereNotIn('platform', $keep)->delete();
        $owner->unsetRelation('socialLinks');
    }

    /**
     * The owner's links as the { platform: url } map the admin form edits.
     * Expects `socialLinks` to be eager loaded.
     *
     * @return array<string, string>
     */
    public function asMap(Studio|Founder $owner): array
    {
        $map = [];

        foreach ($owner->socialLinks as $link) {
            $map[$link->platform] = $link->url;
        }

        return $map;
    }
}
