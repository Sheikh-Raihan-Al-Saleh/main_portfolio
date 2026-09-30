import * as icons from '@lucide/vue';
import { Boxes } from '@lucide/vue';
import type { Component } from 'vue';

/**
 * Resolve a Lucide icon stored by name (e.g. "Network" from the admin's
 * section editor) to its component, with a safe fallback when the name is
 * blank or does not exist in the installed icon set.
 */
export function resolveIcon(name: string | null | undefined): Component {
    if (!name) {
        return Boxes;
    }

    const registry = icons as unknown as Record<string, Component | undefined>;

    return registry[name] ?? Boxes;
}
