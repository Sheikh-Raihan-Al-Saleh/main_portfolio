<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * The one place that reads and writes uploaded media.
 *
 * Files live on the "uploads" disk (public/uploads, served as static files).
 * Only the relative path is stored in the database; URLs are built from it on
 * the fly and are relative ("/uploads/..."), so they work on any host.
 */
class MediaService
{
    public const DISK = 'uploads';

    /**
     * Public URL for a stored path, or null for a blank path.
     *
     * Deliberately does not check the disk: that was a filesystem hit for every
     * image of every serialized model. A missing file is handled by the
     * frontend image component's error fallback instead.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        // Built from config rather than the disk driver, so the URL is always
        // the relative "/uploads/..." form, whatever driver backs the disk.
        $base = rtrim((string) config('filesystems.disks.'.self::DISK.'.url', '/uploads'), '/');

        return $base.'/'.ltrim((string) $path, '/');
    }

    /**
     * Store a new upload in $directory and return its path.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, self::DISK);

        if ($path === false) {
            throw new RuntimeException("Unable to store uploaded file in [{$directory}].");
        }

        return $path;
    }

    /**
     * Resolve the new value of a single media column from a form submission.
     *
     * - A new file replaces (and deletes) the previous one.
     * - The remove flag clears it (and deletes the file).
     * - Otherwise the previous path is kept.
     */
    public function replace(?UploadedFile $file, string $directory, ?string $previous, bool $remove = false): ?string
    {
        if ($file !== null) {
            $path = $this->store($file, $directory);
            $this->delete($previous);

            return $path;
        }

        if ($remove) {
            $this->delete($previous);

            return null;
        }

        return $previous;
    }

    public function delete(?string $path): void
    {
        if (filled($path) && Storage::disk(self::DISK)->exists($path)) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    /**
     * @param  iterable<int, string|null>  $paths
     */
    public function deleteMany(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    public function exists(?string $path): bool
    {
        return filled($path) && Storage::disk(self::DISK)->exists($path);
    }
}
