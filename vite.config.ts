import inertia from '@inertiajs/vite';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
            fonts: [
                bunny('Inter', {
                    weights: [400, 500, 600],
                }),
                bunny('JetBrains Mono', {
                    weights: [400, 500, 600, 700],
                }),
                // Serif display face for the admin panel's headings.
                bunny('Instrument Serif', {
                    weights: [400],
                    styles: ['normal', 'italic'],
                }),
                // The public site: Bricolage Grotesque headlines, DM Sans text.
                bunny('Bricolage Grotesque', {
                    weights: [500, 600, 700, 800],
                }),
                bunny('DM Sans', {
                    weights: [400, 500, 600, 700],
                }),
            ],
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/.agents/**',
                '**/.claude/**',
                '**/.cursor/**',
                '**/.junie/**',
                '**/vendor/**',
            ],
        },
    },
});
