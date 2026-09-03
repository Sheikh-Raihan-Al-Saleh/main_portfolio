<script setup lang="ts">
import { useInView } from 'motion-v';
import { ref, watch } from 'vue';

type Props = {
    value: number;
    suffix?: string;
    duration?: number;
};

const { value, duration = 1400 } = defineProps<Props>();

const element = ref<HTMLElement | null>(null);
const inView = useInView(element);
const displayed = ref(0);
// useInView has no `once` option, so latch the first entry ourselves.
let hasRun = false;

/**
 * Counts up the first time the element scrolls into view. Honours reduced
 * motion by jumping straight to the final value.
 */
watch(inView, (visible) => {
    if (!visible || hasRun) {
        return;
    }

    hasRun = true;

    const reduced =
        typeof window !== 'undefined' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduced || value === 0) {
        displayed.value = value;

        return;
    }

    const start = performance.now();

    const tick = (now: number) => {
        const progress = Math.min((now - start) / duration, 1);
        // Ease-out cubic so the number decelerates into place.
        displayed.value = Math.round(value * (1 - Math.pow(1 - progress, 3)));

        if (progress < 1) {
            requestAnimationFrame(tick);
        }
    };

    requestAnimationFrame(tick);
});
</script>

<template>
    <span ref="element" class="tabular-nums"
        >{{ displayed }}{{ suffix ?? '' }}</span
    >
</template>
