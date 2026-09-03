<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Serve a file from the public storage disk.
     *
     * This bypasses the storage symlink, which is unreliable on shared hosting
     * (CPanel) where `php artisan storage:link` may not persist or may not be
     * executable.
     */
    public function show(string $path): StreamedResponse
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path);
        $size = $disk->size($path);

        return response()->stream(
            function () use ($disk, $path) {
                $stream = $disk->readStream($path);

                if ($stream === null) {
                    abort(500);
                }

                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type' => $mime,
                'Content-Length' => $size,
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ],
        );
    }
}
