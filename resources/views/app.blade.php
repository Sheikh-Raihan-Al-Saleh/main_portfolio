<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: #f7f5f0;
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        {{-- Favicon: the studio's logo mark when one is uploaded, else the bundled icon. --}}
        @php($brand = \App\Models\Studio::brand())
        @php($isAdminApp = request()->is('admin', 'admin/*', 'settings', 'settings/*'))
        @if ($brand['logo_mark_url'])
            <link rel="icon" href="{{ $brand['logo_mark_url'] }}">
            @unless ($isAdminApp)
                <link rel="apple-touch-icon" href="{{ $brand['logo_mark_url'] }}">
            @endunless
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            @unless ($isAdminApp)
                <link rel="apple-touch-icon" href="/apple-touch-icon.png">
            @endunless
        @endif

        {{-- The admin panel installs as an app (PWA): manifest, colours and home-screen icon. --}}
        @if ($isAdminApp)
            <link rel="manifest" href="{{ route('pwaManifest') }}">
            <meta name="theme-color" content="#f6f7f9" media="(prefers-color-scheme: light)">
            <meta name="theme-color" content="#0b0d10" media="(prefers-color-scheme: dark)">
            <meta name="mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-title" content="Admin">
            <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
            <link rel="apple-touch-icon" href="/pwa/apple-touch-icon.png">
        @endif
        <meta property="og:site_name" content="{{ $brand['name'] }}">

        @fonts

        {{-- Named routes for the frontend's route() helper (Ziggy). --}}
        @routes
        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ $brand['name'] }}</title>
        </x-inertia::head>
        {{-- The boot screen needs JavaScript to leave; without it, never show it. --}}
        <noscript><style>.site-loader { display: none !important; }</style></noscript>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
