import type { Component } from 'vue';

/**
 * Every Lucide icon as a lazy loader, keyed by normalised name. Each icon is
 * code-split into its own tiny chunk; this module is itself loaded on demand
 * by iconResolver so the (large) map stays off the first-paint path.
 */
const loaders = import.meta.glob<Component>(
    '/node_modules/@lucide/vue/dist/esm/icons/*.mjs',
    { import: 'default' },
);

/** "ArrowUpRight", "arrow-up-right" and "LucideArrowUpRightIcon" all match. */
export function normaliseIconName(name: string): string {
    return name
        .replace(/^Lucide/, '')
        .replace(/Icon$/, '')
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '');
}

export const iconLoaders = new Map(
    Object.entries(loaders).map(([path, load]) => [
        normaliseIconName(
            path.slice(path.lastIndexOf('/') + 1, -'.mjs'.length),
        ),
        load,
    ]),
);
