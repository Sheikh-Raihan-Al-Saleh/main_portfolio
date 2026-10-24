<script setup lang="ts">
import { useId } from 'vue';

/**
 * A circuit-board backdrop: right-angled traces ending in solder pads, with
 * a pulse of the face's accent travelling along each trace. Pure SVG + CSS
 * (stroke-dashoffset), so it costs no script and stops under reduced motion.
 */
type Props = {
    /** Mirror the layout horizontally, so neighbouring sections differ. */
    flip?: boolean;
};

withDefaults(defineProps<Props>(), { flip: false });

/** Unique per instance: several circuits can share a page. */
const uid = useId();

const traces = [
    'M0 120 H180 L220 160 H420 L460 120 H620',
    'M0 300 H120 L160 260 H340 L380 300 H520 L560 340 H760',
    'M0 470 H240 L280 430 H460',
    'M1200 80 H1020 L980 120 H820 L780 80 H700',
    'M1200 260 H1060 L1020 300 H860 L820 260 H660',
    'M1200 420 H980 L940 460 H780 L740 420 H620 L580 460 H520',
    'M1200 560 H1080 L1040 520 H900',
    'M300 0 V60 L340 100 V180',
    'M900 600 V540 L860 500 V400',
];

const pads = [
    [620, 120],
    [760, 340],
    [460, 430],
    [700, 80],
    [660, 260],
    [520, 460],
    [900, 520],
    [340, 180],
    [860, 400],
];
</script>

<template>
    <svg
        class="pointer-events-none absolute inset-0 size-full"
        :class="flip ? '-scale-x-100' : ''"
        viewBox="0 0 1200 600"
        preserveAspectRatio="xMidYMid slice"
        aria-hidden="true"
    >
        <defs>
            <linearGradient :id="`${uid}-pulse`" x1="0" x2="1">
                <stop
                    offset="0"
                    stop-color="var(--grad-from)"
                    stop-opacity="0"
                />
                <stop offset="0.5" stop-color="var(--grad-from)" />
                <stop offset="1" stop-color="var(--grad-to)" />
            </linearGradient>
            <radialGradient :id="`${uid}-fade`" cx="0.5" cy="0.5" r="0.6">
                <stop offset="0.3" stop-color="#fff" />
                <stop offset="1" stop-color="#fff" stop-opacity="0" />
            </radialGradient>
            <mask :id="`${uid}-mask`">
                <rect width="1200" height="600" :fill="`url(#${uid}-fade)`" />
            </mask>
        </defs>

        <g
            :mask="`url(#${uid}-mask)`"
            fill="none"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path
                v-for="(d, i) in traces"
                :key="`t-${i}`"
                :d="d"
                class="ui-trace"
            />
            <path
                v-for="(d, i) in traces"
                :key="`p-${i}`"
                :d="d"
                pathLength="1000"
                class="ui-trace-pulse"
                :stroke="`url(#${uid}-pulse)`"
                :style="{
                    animationDelay: `${i * -0.9}s`,
                    animationDuration: `${5 + (i % 3)}s`,
                }"
            />
            <g v-for="([x, y], i) in pads" :key="`d-${i}`">
                <circle :cx="x" :cy="y" r="7" class="ui-trace-pad" />
                <circle :cx="x" :cy="y" r="2.5" class="ui-trace-core" />
            </g>
        </g>
    </svg>
</template>
