<?php

namespace App\Http\Middleware;

use App\Http\Resources\FounderResource;
use App\Http\Resources\StudioResource;
use App\Services\ContactMessageService;
use App\Services\FounderService;
use App\Services\StudioService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // The company name and logos, used on every page: header, footer,
            // loader, login, admin sidebar and page titles. Cached in StudioService.
            'brand' => fn () => app(StudioService::class)->brand(),
            // The studio and founder records, for the public header, footer and
            // contact details. One query each per request (memoised).
            'studio' => fn () => StudioResource::make(app(StudioService::class)->current())->resolve(),
            'founder' => fn () => FounderResource::make(app(FounderService::class)->current())->resolve(),
            // Only counted for signed-in admins, so public pages skip the query.
            'unreadMessages' => fn () => $request->user()?->is_admin
                ? app(ContactMessageService::class)->unreadCount()
                : 0,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            // PHP's upload limits, so admin file pickers can reject an oversized
            // file with a clear message. PHP otherwise drops it before Laravel
            // sees it and the upload just fails to appear.
            'uploadLimits' => fn () => $request->user()?->is_admin
                ? $this->uploadLimits()
                : null,
        ];
    }

    /**
     * Per-file and per-request upload limits in bytes; null means unlimited.
     *
     * @return array{file: int|null, request: int|null}
     */
    private function uploadLimits(): array
    {
        $bytes = fn (string $key): ?int => ($value = ini_parse_quantity((string) ini_get($key))) > 0
            ? $value
            : null;

        $request = $bytes('post_max_size');
        $file = $bytes('upload_max_filesize');

        // A single file can never be larger than the whole request.
        if ($request !== null && ($file === null || $request < $file)) {
            $file = $request;
        }

        return ['file' => $file, 'request' => $request];
    }
}
