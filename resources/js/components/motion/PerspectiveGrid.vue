<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A receding 3D grid — the Studio's structural backdrop.
 *
 * It is drawn with two repeating linear gradients on one plane that is rotated
 * away from the viewer inside a perspective container, so the grid appears to
 * run to a horizon. There is no canvas, no shader and no per-frame JavaScript:
 * the optional drift is a single `background-position` animation, and the
 * fade-out to the horizon is a mask.
 */
type Props = {
    /** Cell size of the grid, in pixels. */
    size?: number;
    /** X-axis rotation of the plane; larger values lie flatter to the viewer. */
    tilt?: number;
    /** Metres of travel per loop. 0 parks the grid. */
    drift?: number;
    /** Seconds for one drift loop. */
    duration?: number;
    /** Scales the plane so it fills the frame at the given tilt. */
    scale?: number;
    /** Horizon fade direction. */
    fade?: 'top' | 'bottom' | 'both';
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    size: 52,
    tilt: 68,
    drift: 52,
    duration: 26,
    scale: 2.4,
    fade: 'top',
    class: undefined,
});

const style = computed(() => ({
    '--grid-size': `${props.size}px`,
    '--grid-tilt': `${props.tilt}deg`,
    '--grid-scale': `${props.scale}`,
    '--grid-duration': `${props.duration}s`,
    '--grid-drift': props.drift > 0 ? 'running' : 'paused',
}));

const maskStyle = computed(() => {
    const edge = 'transparent';
    const solid = 'black';

    const stops =
        props.fade === 'top'
            ? `linear-gradient(to bottom, ${solid} 0%, ${edge} 78%)`
            : props.fade === 'bottom'
              ? `linear-gradient(to top, ${solid} 0%, ${edge} 78%)`
              : `linear-gradient(to bottom, ${edge} 0%, ${solid} 30%, ${solid} 60%, ${edge} 100%)`;

    return {
        maskImage: stops,
        WebkitMaskImage: stops,
    };
});
</script>

<template>
    <div
        :class="cn('pointer-events-none absolute inset-0 overflow-hidden', props.class)"
        aria-hidden="true"
        style="perspective: 700px"
    >
        <div class="perspective-grid-plane" :style="{ ...style, ...maskStyle }" />
    </div>
</template>

<style scoped>
.perspective-grid-plane {
    position: absolute;
    inset: -60%;
    transform: rotateX(var(--grid-tilt, 68deg)) scale(var(--grid-scale, 2.4));
    transform-origin: 50% 50%;
    background-image:
        linear-gradient(
            to right,
            color-mix(in oklab, var(--border) 90%, transparent) 1px,
            transparent 1px
        ),
        linear-gradient(
            to bottom,
            color-mix(in oklab, var(--border) 90%, transparent) 1px,
            transparent 1px
        );
    background-size: var(--grid-size, 52px) var(--grid-size, 52px);
    opacity: 0.55;
}

@media (prefers-reduced-motion: no-preference) {
    .perspective-grid-plane {
        animation: grid-travel linear infinite var(--grid-duration, 26s);
        animation-play-state: var(--grid-drift, running);
    }
}

@keyframes grid-travel {
    from {
        background-position:
            0 0,
            0 0;
    }
    to {
        background-position:
            0 var(--grid-size, 52px),
            0 var(--grid-size, 52px);
    }
}

@media (prefers-reduced-motion: reduce) {
    .perspective-grid-plane {
        animation: none;
    }
}
</style>
