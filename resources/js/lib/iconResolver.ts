import { Boxes } from '@lucide/vue';
import { defineAsyncComponent, markRaw } from 'vue';
import type { Component } from 'vue';

/** Resolved icons per fallback, so re-renders reuse the same component. */
const cache = new WeakMap<Component, Map<string, Component>>();

/**
 * Resolve a Lucide icon stored by name (e.g. "Network" from the admin's
 * section editor) to its component, with a safe fallback when the name is
 * blank or does not exist in the installed icon set.
 *
 * Importing the whole `@lucide/vue` namespace would ship all ~1,800 icons
 * (600 kB) to every visitor, so the icon is fetched on demand instead.
 */
export function resolveIcon(
    name: string | null | undefined,
    fallback: Component = Boxes,
): Component {
    if (!name) {
        return fallback;
    }

    let icons = cache.get(fallback);

    if (!icons) {
        icons = new Map();
        cache.set(fallback, icons);
    }

    let icon = icons.get(name);

    if (!icon) {
        icon = markRaw(
            defineAsyncComponent({
                loader: async () => {
                    const { iconLoaders, normaliseIconName } =
                        await import('@/lib/lucideIcons');
                    const load = iconLoaders.get(normaliseIconName(name));

                    return load ? load() : fallback;
                },
                errorComponent: fallback,
            }),
        );
        icons.set(name, icon);
    }

    return icon;
}
