<script setup lang="ts">
import { computed } from 'vue';

/**
 * Scroll ticker — a band of studio phrases that slides sideways as the page
 * scrolls.
 *
 * This is the cheapest kind of scroll motion there is: no JavaScript, no
 * scroll listener and no per-frame work in the main thread. The browser maps
 * scroll position directly onto the animation (CSS scroll timelines), so the
 * movement is handed to the compositor.
 *
 * Where scroll timelines are unsupported, or for reduced-motion users, the
 * band simply sits still — it still reads as a designed element.
 *
 * Decorative: `aria-hidden`, because the same names appear in the hero,
 * header and footer as real content.
 */

type Props = {
    /** Phrases to run through the band. Blanks are dropped. */
    items?: string[];
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    items: () => [],
    class: undefined,
});

const phrases = computed(() => {
    const list = props.items.map((item) => item.trim()).filter(Boolean);

    return list.length ? list : ['Software studio'];
});

/**
 * Three passes of the same phrases. The track drifts by exactly one pass, so
 * the band is always covered by an identical copy — the movement reads as a
 * continuous ribbon rather than something running off the edge.
 */
const passes = [0, 1, 2];
</script>

<template>
    <div
        :class="props.class"
        class="ticker border-y border-border bg-muted/30"
        aria-hidden="true"
    >
        <div class="ticker-track">
            <span
                v-for="pass in passes"
                :key="pass"
                class="ticker-pass"
            >
                <span
                    v-for="(phrase, index) in phrases"
                    :key="`${pass}-${index}`"
                    class="ticker-item"
                >
                    {{ phrase }}
                    <em class="ticker-sep">✳</em>
                </span>
            </span>
        </div>
    </div>
</template>

<style scoped>
.ticker {
    position: relative;
    overflow: hidden;
    padding-block: 0.85rem;
    /* Keeps text crisp while the track is being transformed. */
    -webkit-mask-image: linear-gradient(
        to right,
        transparent,
        black 4rem,
        black calc(100% - 4rem),
        transparent
    );
    mask-image: linear-gradient(
        to right,
        transparent,
        black 4rem,
        black calc(100% - 4rem),
        transparent
    );
}

.ticker-track {
    display: flex;
    width: max-content;
    will-change: transform;
}

.ticker-pass {
    display: flex;
    align-items: center;
}

.ticker-item {
    display: inline-flex;
    align-items: center;
    gap: 1.5rem;
    padding-inline: 1.5rem;
    font-family: var(--font-display);
    font-size: clamp(1.0625rem, 2.4vw, 1.75rem);
    letter-spacing: -0.01em;
    white-space: nowrap;
    color: var(--muted-foreground);
}

.ticker-sep {
    font-style: normal;
    font-size: 0.6em;
    color: var(--brand);
}

/*
 * Scroll-linked drift. `animation-duration` is present only to satisfy the
 * shorthand parse — the scroll timeline supersedes it.
 */
@supports (animation-timeline: scroll(root)) {
    @media (prefers-reduced-motion: no-preference) {
        .ticker-track {
            animation-name: ticker-drift;
            animation-duration: 1ms;
            animation-timing-function: linear;
            animation-fill-mode: both;
            animation-timeline: scroll(root block);
        }
    }
}

@keyframes ticker-drift {
    from {
        transform: translate3d(0, 0, 0);
    }
    to {
        /* Exactly one pass: the third copy lands where the first began. */
        transform: translate3d(-33.3333%, 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .ticker-track {
        animation: none;
    }
}
</style>
