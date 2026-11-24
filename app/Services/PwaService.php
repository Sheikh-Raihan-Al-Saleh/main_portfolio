<?php

namespace App\Services;

use App\Models\Studio;

/**
 * The admin panel as an installable app: its web manifest and its service
 * worker, versioned by the current front-end build.
 */
class PwaService
{
    /** The service worker source; "__VERSION__" is replaced when served. */
    private const WORKER_PATH = 'resources/pwa/sw.js';

    /**
     * The web app manifest for the admin panel.
     *
     * @return array<string, mixed>
     */
    public function manifest(): array
    {
        $brand = Studio::brand();

        return [
            'id' => '/admin',
            'name' => "{$brand['name']} Admin",
            'short_name' => 'Admin',
            'description' => "Content studio for {$brand['name']}: projects, clients, pages and messages.",
            'start_url' => '/admin',
            'scope' => '/',
            'display' => 'standalone',
            'display_override' => ['window-controls-overlay', 'standalone'],
            'orientation' => 'any',
            'background_color' => '#0b0d10',
            'theme_color' => '#0b0d10',
            'categories' => ['productivity', 'business'],
            'icons' => [
                ['src' => '/pwa/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/pwa/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Messages', 'url' => route('messageIndex', absolute: false), 'icons' => [['src' => '/pwa/icon-192.png', 'sizes' => '192x192']]],
                ['name' => 'New project', 'url' => route('projectCreate', absolute: false), 'icons' => [['src' => '/pwa/icon-192.png', 'sizes' => '192x192']]],
            ],
        ];
    }

    /**
     * The service worker script with its version filled in.
     */
    public function serviceWorker(): string
    {
        return str_replace('__VERSION__', $this->version(), file_get_contents(base_path(self::WORKER_PATH)));
    }

    /**
     * A short hash of the current build and the worker source. A deploy
     * changes it, so the browser sees a new worker and updates on its own.
     */
    public function version(): string
    {
        $build = public_path('build/manifest.json');

        $fingerprint = (is_file($build) ? md5_file($build) : 'dev')
            .md5_file(base_path(self::WORKER_PATH));

        return substr(md5($fingerprint), 0, 12);
    }
}
