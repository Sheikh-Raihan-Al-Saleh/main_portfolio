<?php

namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Stores uploaded media on the public disk and cleans up whatever it replaced,
 * so editing a record repeatedly does not leave orphaned files behind.
 */
trait HandlesMediaUploads
{
    /**
     * Store $file in $directory and delete $previousPath. Returns the new path,
     * or $previousPath unchanged when no file was uploaded.
     */
    protected function storeMedia(?UploadedFile $file, string $directory, ?string $previousPath = null): ?string
    {
        if ($file === null) {
            return $previousPath;
        }

        $path = $this->putMedia($file, $directory);

        $this->deleteMedia($previousPath);

        return $path;
    }

    /**
     * Write a file to the public uploads directory, failing loudly if the disk
     * rejects it rather than silently persisting a `false` path on the model.
     */
    protected function putMedia(UploadedFile $file, string $directory): string
    {
        $path = $file->store($directory, 'uploads');

        if ($path === false) {
            throw new RuntimeException("Unable to store uploaded file in [{$directory}].");
        }

        return $path;
    }

    protected function deleteMedia(?string $path): void
    {
        if (filled($path) && Storage::disk('uploads')->exists($path)) {
            Storage::disk('uploads')->delete($path);
        }
    }

    /**
     * Delete a list of stored paths, ignoring any that are already gone.
     *
     * @param  array<int, string>|null  $paths
     */
    protected function deleteMediaMany(?array $paths): void
    {
        foreach ($paths ?? [] as $path) {
            $this->deleteMedia($path);
        }
    }
}
