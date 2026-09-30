<script setup lang="ts">
import { motion, useTransform } from 'motion-v';
import { computed, ref } from 'vue';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { cn } from '@/lib/utils';

/**
 * Scroll-linked depth layer.
 *
 * Where `ScrollReveal` fires once, this *tracks*: the element's own travel
 * through the viewport is mapped straight onto a transform, so it drifts at a
 * different rate from the page around it and the section reads as layers.
 *
 * The mapping is a motion value bound to the element's style, so a scroll
 * gesture writes one transform per frame and never re-renders the component.
 */
type Props = {
    /** Pixels travelled over the element's full pass through the viewport. */
    distance?: number;
    /** Extra Z offset in pixels — positive pushes the layer toward the viewer. */
    depth?: number;
    /** Cross-axis drift, as px at the end of the pass. */
    drift?: number;
    /** Scale at the end of the pass. 1 disables the scale. */
    scaleTo?: number;
    /** Degrees of rotation accumulated across the pass. */
    rotateTo?: number;
    /** Fade the layer out over the last part of its pass. */
    fadeOut?: boolean;
    /** Class for the tracked element itself — use for absolute positioning. */
    rootClass?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    distance: 60,
    depth: 0,
    drift: 0,
    scaleTo: 1,
    rotateTo: 0,
    fadeOut: false,
    rootClass: undefined,
    class: undefined,
});

/** The element whose passage through the viewport drives the mapping. */
const root = ref<HTMLElement | null>(null);

const { progress } = useScrollProgress({
    target: root,
    offset: ['start end', 'end start'],
});

/** The layer starts below its resting place and ends above it. */
const y = useTransform(progress, [0, 1], [props.distance, -props.distance]);
const x = useTransform(progress, [0, 1], [0, props.drift]);
const z = useTransform(progress, [0, 1], [0, props.depth]);
const scale = useTransform(progress, [0, 1], [1, props.scaleTo]);
const rotate = useTransform(progress, [0, 1], [0, props.rotateTo]);
const opacity = useTransform(
    progress,
    props.fadeOut ? [0, 0.6, 1] : [0, 1],
    props.fadeOut ? [1, 1, 0.35] : [1, 1],
);

const style = computed(() => ({
    y,
    x,
    z,
    scale,
    rotate,
    ...(props.fadeOut ? { opacity } : {}),
}));
</script>

<template>
    <!-- The tracked wrapper is the element whose position drives the mapping,
         so it must be the untransformed box in normal flow. -->
    <div
        ref="root"
        :class="cn('will-change-transform', props.rootClass)"
        style="perspective: 1200px"
    >
        <motion.div
            :class="cn('[transform-style:preserve-3d]', props.class)"
            :style="style"
        >
            <slot />
        </motion.div>
    </div>
</template>
