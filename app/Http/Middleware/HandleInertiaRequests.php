<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Profile;
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
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            // Shared on every request so the layout's nav and footer always
            // have profile data, whatever page is being rendered.
            'profile' => fn () => Profile::current(),
            // Likewise for the company, so the public header and footer can
            // brand themselves as the studio on the home route and as the
            // founder on /about without each page re-sending them.
            'company' => fn () => Company::current(),
            // Only queried for signed-in admins, so public page loads stay query-free.
            'unreadMessages' => fn () => $request->user()?->is_admin
                ? ContactMessage::unread()->count()
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
