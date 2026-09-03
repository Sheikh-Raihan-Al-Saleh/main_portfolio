<?php

namespace App\Concerns;

/**
 * Generate URLs for stored media files via the /media/ route, which reads
 * directly from the storage disk, bypassing the public/storage symlink that
 * is unreliable on shared hosting (CPanel).
 *
 * This ensures all media displays properly on all hosting environments.
 */
trait ResolvesMediaUrls
{
    /**
     * Resolve a stored media path to a public URL.
     *
     * Returns null for blank paths; returns a /media/ route URL otherwise.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        // Serve via /media/{path} which reads directly from the storage disk,
        // bypassing the public/storage symlink that is unreliable on CPanel.
        return url('media/'.$path);
    }
}
