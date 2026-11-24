<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores, replaces and deletes uploaded files.
 *
 * Every upload lives on the "uploads" disk (public/uploads, served as static
 * files). Only the relative path is saved in the database.
 */
class FileService
{
    /**
     * The disk every upload is stored on.
     */
    public const DISK = 'uploads';

    /**
     * Public URL of a stored file, or null for a blank path.
     *
     * Built from config, so it is always the relative "/uploads/..." form and
     * works on any host. The disk is not checked: a missing file is handled by
     * the frontend image component's fallback instead.
     */
    public static function url(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $base = rtrim((string) config('filesystems.disks.'.self::DISK.'.url', '/uploads'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * Store an upload in $directory and return its path.
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
     * The new value of one file column after a form submission: a new file
     * replaces (and deletes) the previous one, the remove flag clears it,
     * otherwise the previous path is kept.
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

    /**
     * Delete a stored file, if the path is set and the file exists.
     */
    public function delete(?string $path): void
    {
        if ($this->exists($path)) {
            Storage::disk(self::DISK)->delete((string) $path);
        }
    }

    /**
     * Delete several stored files.
     *
     * @param  iterable<int, string|null>  $paths
     */
    public function deleteMany(iterable $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }

    /**
     * Whether a stored file exists for this path.
     */
    public function exists(?string $path): bool
    {
        return filled($path) && Storage::disk(self::DISK)->exists((string) $path);
    }
}
