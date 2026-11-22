<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | The disk the framework uses when none is named. Application media never
    | relies on this: every upload goes through App\Services\Media\MediaService,
    | which always writes to the "uploads" disk below.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        // Private application files (exports, temporary files). Not web-served.
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'throw' => false,
            'report' => false,
        ],

        // Uploaded media (logos, covers, gallery images, videos, resume).
        //
        // It lives inside the web root so the web server serves the files as
        // plain static assets: no symlink and no PHP route. The URL is kept
        // relative ("/uploads/...") so the same stored path works on any host.
        //
        // The root is re-pointed at runtime by AppServiceProvider so it follows
        // the real web root on shared hosting (cPanel public_html).
        'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
            'url' => '/uploads',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    ],

    // No storage:link symlinks: uploads are served directly from public/uploads.
    'links' => [],

];
