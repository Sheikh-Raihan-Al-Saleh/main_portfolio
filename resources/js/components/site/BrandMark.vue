<script setup lang="ts">
import { cn } from '@/lib/utils';

/**
 * The site's brand mark: the uploaded website logo, or the owner's initials
 * when there is none. Shared by both faces' headers and footers so the same
 * artwork appears everywhere.
 *
 * The light chip behind an uploaded logo keeps dark-on-light artwork legible
 * in dark mode.
 */
type Props = {
    logoUrl: string | null;
    name?: string | null;
    initials?: string;
    /** Extra classes on the initials tile (the footer marks it `footer-logo-icon`). */
    initialsClass?: string;
    /** Height class for the logo image. */
    imageClass?: string;
    showName?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    name: null,
    initials: '',
    initialsClass: undefined,
    imageClass: 'h-12',
    showName: true,
});
</script>

<template>
    <span
        v-if="props.logoUrl"
        class="inline-flex items-center rounded-lg bg-white px-2.5 py-1 shadow-sm ring-1 ring-black/5"
    >
        <!-- Width caps are generous so a wide logo scales by height, not shrinks. -->
        <img
            :src="props.logoUrl"
            :alt="props.name ?? 'Home'"
            decoding="async"
            :class="
                cn(
                    'w-auto max-w-56 object-contain sm:max-w-72',
                    props.imageClass,
                )
            "
        />
    </span>
    <span v-else class="inline-flex items-center gap-2.5">
        <span
            :class="
                cn(
                    'grid size-9 place-items-center rounded-md bg-foreground text-sm font-bold text-background',
                    props.initialsClass,
                )
            "
        >
            {{ props.initials || '{ }' }}
        </span>
        <span
            v-if="props.showName && props.name"
            class="text-sm font-semibold tracking-tight"
        >
            {{ props.name }}
        </span>
    </span>
</template>
