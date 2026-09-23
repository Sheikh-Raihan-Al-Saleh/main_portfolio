<?php

namespace App\Concerns;

/**
 * Generate URLs for stored media files. Uploads live in public/uploads and
 * are served as static files by the web server, so no symlink or dynamic
 * route is involved and the same URL works in local and production.
 */
trait ResolvesMediaUrls
{
    /**
     * Resolve a stored media path to a public URL.
     *
     * Returns null for blank paths; returns an /uploads/ URL otherwise.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return url('uploads/'.$path);
    }
}
