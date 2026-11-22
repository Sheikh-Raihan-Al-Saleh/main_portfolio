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
        @php($brand = app(\App\Services\StudioService::class)->brand())
        @if ($brand['logo_mark_url'])
            <link rel="icon" href="{{ $brand['logo_mark_url'] }}">
            <link rel="apple-touch-icon" href="{{ $brand['logo_mark_url'] }}">
        @else
            <link rel="icon" href="/favicon.ico" sizes="any">
            <link rel="icon" href="/favicon.svg" type="image/svg+xml">
            <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        @endif
        <meta property="og:site_name" content="{{ $brand['name'] }}">

        @fonts

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
