<script setup lang="ts">
import { useInView } from 'motion-v';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import type { AnimatedCounterProps } from '@/types';

const { value, duration = 1400 } = defineProps<AnimatedCounterProps>();

const element = ref<HTMLElement | null>(null);
const inView = useInView(element);
// Start from the real figure so the server-rendered HTML (and any visitor
// without JavaScript) never sees a placeholder zero.
const displayed = ref(value);
// useInView has no `once` option, so latch the first entry ourselves.
let hasRun = false;
// The pending animation frame, so it can be cancelled if the visitor leaves
// the page (an SPA visit) while the number is still counting.
let animationFrameId: number | null = null;

function prefersReducedMotion() {
    return (
        typeof window !== 'undefined' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    );
}

onMounted(() => {
    const rect = element.value?.getBoundingClientRect();
    const visibleOnArrival =
        rect !== undefined && rect.top < window.innerHeight && rect.bottom > 0;

    // Already on screen, or motion is unwelcome: show the final value rather
    // than snapping it back to zero in front of the visitor.
    if (visibleOnArrival || prefersReducedMotion()) {
        displayed.value = value;
        hasRun = true;
    }
});

/**
 * Counts up the first time the element scrolls into view. Honours reduced
 * motion by jumping straight to the final value.
 */
watch(inView, (visible) => {
    if (!visible || hasRun) {
        return;
    }

    hasRun = true;

    if (prefersReducedMotion() || value === 0) {
        displayed.value = value;

        return;
    }

    displayed.value = 0;

    const start = performance.now();

    const tick = (now: number) => {
        const progress = Math.min((now - start) / duration, 1);
        // Ease-out cubic so the number decelerates into place.
        displayed.value = Math.round(value * (1 - Math.pow(1 - progress, 3)));

        animationFrameId = progress < 1 ? requestAnimationFrame(tick) : null;
    };

    animationFrameId = requestAnimationFrame(tick);
});

onBeforeUnmount(() => {
    if (animationFrameId !== null) {
        cancelAnimationFrame(animationFrameId);
    }
});
</script>

<template>
    <span ref="element" class="tabular-nums"
        >{{ displayed }}{{ suffix ?? '' }}</span
    >
</template>
