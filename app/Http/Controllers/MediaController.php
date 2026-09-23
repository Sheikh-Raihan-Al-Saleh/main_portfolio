<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    /**
     * Legacy /media/{path} endpoint kept so old cached URLs keep working.
     *
     * New code links straight to /uploads/{path} (static files). This route
     * streams from the uploads disk, falling back to the pre-migration
     * storage/app/public disk so nothing404s between deploying this change
     * and running `php artisan media:migrate`.
     */
    public function show(string $path): StreamedResponse
    {
        $disk = Storage::disk('uploads');

        if (! $disk->exists($path)) {
            $legacy = Storage::disk('public');

            if ($legacy->exists($path)) {
                $disk = $legacy;
            } else {
                abort(404);
            }
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
