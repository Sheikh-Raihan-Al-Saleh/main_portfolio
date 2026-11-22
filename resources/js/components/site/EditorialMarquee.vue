<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A giant band of running condensed type, separated by a star. Used for the
 * studio's ticker phrases and the founder's roles.
 *
 * The phrases are repeated so the loop is seamless; the copies are hidden
 * from assistive tech, which reads the plain list once. Reduced motion stops
 * the band (.ui-marquee in site.css).
 */
type Props = {
    items: string[];
    tone?: 'accent' | 'ink';
    reverse?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    tone: 'accent',
    reverse: false,
    class: undefined,
});

const phrases = computed(() =>
    props.items.map((item) => item.trim()).filter(Boolean),
);

/** Enough repeats that one copy is wider than any screen. */
const run = computed(() => {
    const base = phrases.value;
    const repeats = Math.max(1, Math.ceil(6 / Math.max(base.length, 1)));

    return Array.from({ length: repeats }, () => base).flat();
});

const duration = computed(() => `${Math.max(20, run.value.length * 5)}s`);
</script>

<template>
    <div
        v-if="phrases.length"
        :class="
            cn(
                'overflow-hidden border-y py-5 sm:py-7',
                tone === 'accent'
                    ? 'border-transparent bg-[var(--accent-fill)] text-[var(--accent-ink)]'
                    : 'border-border text-foreground',
                props.class,
            )
        "
        data-testid="editorial-marquee"
    >
        <p class="sr-only">{{ phrases.join(', ') }}</p>
        <div
            class="ui-marquee ui-marquee-xl"
            :style="{
                '--marquee-duration': duration,
                animationDirection: reverse ? 'reverse' : undefined,
            }"
            aria-hidden="true"
        >
            <template v-for="copy in 2" :key="copy">
                <span
                    v-for="(phrase, i) in run"
                    :key="`${copy}-${i}`"
                    class="flex shrink-0 items-center"
                >
                    <span class="px-6 sm:px-10">{{ phrase }}</span>
                    <svg
                        viewBox="0 0 24 24"
                        class="size-[0.45em] shrink-0"
                        fill="currentColor"
                    >
                        <path
                            d="M12 0l2.6 8.4L24 12l-9.4 3.6L12 24l-2.6-8.4L0 12l9.4-3.6z"
                        />
                    </svg>
                </span>
            </template>
        </div>
    </div>
</template>
