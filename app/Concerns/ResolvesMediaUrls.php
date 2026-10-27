<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;

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
     * Returns null for blank paths and for files that are no longer on disk,
     * so the page falls back to its placeholder instead of a broken image.
     */
    protected function mediaUrl(?string $path): ?string
    {
        if (! $this->mediaExists($path)) {
            return null;
        }

        return url('uploads/'.$path);
    }

    protected function mediaExists(?string $path): bool
    {
        return filled($path) && Storage::disk('uploads')->exists($path);
    }
}
