<?php

namespace App\Http\Controllers\Pwa;

use App\Http\Controllers\Controller;
use App\Services\PwaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Public: the admin panel's web manifest and service worker. Browsers fetch
 * both without the session, so neither sits behind auth; neither holds
 * anything private.
 */
class PwaController extends Controller
{
    public function __construct(private PwaService $pwa) {}

    /**
     * The web app manifest.
     */
    public function manifest(): JsonResponse
    {
        return response()
            ->json($this->pwa->manifest(), options: JSON_UNESCAPED_SLASHES)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * The service worker. Never cached, so update checks always see the
     * latest version.
     */
    public function serviceWorker(): Response
    {
        return response($this->pwa->serviceWorker())
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Service-Worker-Allowed', '/');
    }
}
