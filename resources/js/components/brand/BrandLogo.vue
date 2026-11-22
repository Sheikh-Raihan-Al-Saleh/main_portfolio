<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { useBrand } from '@/composables/useSite';
import { cn } from '@/lib/utils';

/**
 * The company logo, used everywhere the brand appears (header, footer,
 * loader, login, admin sidebar).
 *
 * The name and logo files come only from the studio record (Admin → Studio).
 * When no logo is uploaded, or the file fails to load, a typographic wordmark
 * built from the name is shown instead, so the brand is never blank.
 */
type Props = {
    /** Full logo, or the square mark (also the favicon). */
    variant?: 'full' | 'mark';
    size?: 'sm' | 'md' | 'lg' | 'xl' | 'hero';
    /** "dark" picks the logo made for dark backgrounds, if one was uploaded. */
    tone?: 'light' | 'dark';
    /** Show the name next to a mark-only logo. */
    withName?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    variant: 'full',
    size: 'md',
    tone: 'light',
    withName: false,
    class: undefined,
});

const brand = useBrand();
const failed = ref(false);

const src = computed(() => {
    const b = brand.value;

    if (props.variant === 'mark') {
        return b.logo_mark_url ?? b.logo_url;
    }

    return props.tone === 'dark'
        ? (b.logo_dark_url ?? b.logo_url)
        : (b.logo_url ?? b.logo_mark_url);
});

watch(src, () => (failed.value = false));

/** Heights in px, chosen so the logo is always large and legible. */
const heights = {
    sm: 'h-8',
    md: 'h-10 sm:h-12',
    lg: 'h-11 sm:h-14',
    xl: 'h-16 sm:h-20',
    hero: 'h-24 sm:h-32 lg:h-40',
} as const;

const textSizes = {
    sm: 'text-lg',
    md: 'text-xl sm:text-2xl',
    lg: 'text-2xl sm:text-3xl',
    xl: 'text-3xl sm:text-4xl',
    hero: 'text-5xl sm:text-7xl',
} as const;

const initials = computed(() =>
    brand.value.name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase())
        .join(''),
);
</script>

<template>
    <span
        :class="cn('inline-flex shrink-0 items-center gap-3', props.class)"
        data-brand-logo
    >
        <img
            v-if="src && !failed"
            :src="src"
            :alt="`${brand.name} logo`"
            :class="cn('w-auto max-w-none object-contain', heights[size])"
            decoding="async"
            @error="failed = true"
        />

        <!-- Fallback: a monogram tile, plus the name for the full variant. -->
        <span
            v-else
            :class="
                cn(
                    'grid aspect-square place-items-center rounded-[22%] bg-ink font-semibold text-paper',
                    heights[size],
                    tone === 'dark' && 'bg-paper text-ink',
                )
            "
            aria-hidden="true"
        >
            <span
                :class="
                    cn(
                        'leading-none',
                        size === 'hero'
                            ? 'text-4xl sm:text-5xl'
                            : 'text-sm sm:text-base',
                    )
                "
            >
                {{ initials }}
            </span>
        </span>

        <span
            v-if="withName || (variant === 'full' && (!src || failed))"
            :class="
                cn(
                    'display leading-none',
                    textSizes[size],
                    tone === 'dark' ? 'text-paper' : 'text-ink',
                )
            "
        >
            {{ brand.name }}
        </span>
        <span v-else-if="!src || failed" class="sr-only">{{ brand.name }}</span>
    </span>
</template>
