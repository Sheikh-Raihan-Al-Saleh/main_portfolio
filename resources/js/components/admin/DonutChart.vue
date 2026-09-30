<script setup lang="ts">
import { computed } from 'vue';

/**
 * A small donut chart drawn as plain SVG strokes.
 *
 * Same rationale as LineChart: one visual, no library. Each slice is a
 * stroke-dasharray circle segment; the legend doubles as the data table.
 */
type Props = {
    slices: {
        label: string;
        value: number;
        color: string;
    }[];
    /** Label in the hole; defaults to the total. */
    centerLabel?: string;
    size?: number;
};

const props = withDefaults(defineProps<Props>(), {
    centerLabel: undefined,
    size: 148,
});

const total = computed(() =>
    props.slices.reduce((sum, slice) => sum + slice.value, 0),
);

const radius = computed(() => (props.size - 24) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);

/** Stroke geometry per slice, accumulated around the ring. */
const segments = computed(() => {
    let offset = 0;

    return props.slices.map((slice) => {
        const fraction =
            total.value > 0 ? slice.value / total.value : 0;
        const length = fraction * circumference.value;
        const segment = {
            slice,
            length,
            gap: circumference.value - length,
            offset: -offset,
            percent: Math.round(fraction * 100),
        };

        offset += length;

        return segment;
    });
});

const centerText = computed(
    () => props.centerLabel ?? `${total.value} total`,
);
</script>

<template>
    <div class="flex items-center gap-5">
        <svg
            :width="props.size"
            :height="props.size"
            :viewBox="`0 0 ${props.size} ${props.size}`"
            role="img"
            :aria-label="props.slices.map((slice) => `${slice.label}: ${slice.value}`).join(', ')"
            class="shrink-0 -rotate-90"
        >
            <circle
                :cx="props.size / 2"
                :cy="props.size / 2"
                :r="radius"
                fill="none"
                class="stroke-muted"
                stroke-width="12"
            />

            <circle
                v-for="(segment, index) in segments"
                v-show="segment.length > 0"
                :key="`${segment.slice.label}-${index}`"
                :cx="props.size / 2"
                :cy="props.size / 2"
                :r="radius"
                fill="none"
                :stroke="segment.slice.color"
                stroke-width="12"
                stroke-linecap="butt"
                :stroke-dasharray="`${segment.length} ${segment.gap}`"
                :stroke-dashoffset="segment.offset"
                class="transition-[stroke-dasharray] duration-700"
            >
                <title>{{ `${segment.slice.label}: ${segment.slice.value}` }}</title>
            </circle>
        </svg>

        <div class="relative">
            <!-- Center label, absolutely placed over the hole -->
            <div
                class="pointer-events-none absolute -translate-x-1/2 -translate-y-1/2 text-center"
                :style="{ left: `${props.size / 2}px`, top: `${props.size / 2}px` }"
            >
                <p class="text-xl font-bold tabular-nums">
                    {{ total }}
                </p>
                <p class="text-[10px] text-muted-foreground uppercase">
                    {{ centerText }}
                </p>
            </div>
        </div>

        <ul class="min-w-0 flex-1 space-y-2">
            <li
                v-for="segment in segments"
                :key="segment.slice.label"
                class="flex items-center justify-between gap-2 text-sm"
            >
                <span class="inline-flex min-w-0 items-center gap-2">
                    <span
                        class="size-2.5 shrink-0 rounded-sm"
                        :style="{ backgroundColor: segment.slice.color }"
                    />
                    <span class="truncate text-muted-foreground">
                        {{ segment.slice.label }}
                    </span>
                </span>
                <span class="shrink-0 font-medium tabular-nums">
                    {{ segment.slice.value }}
                    <span class="text-xs text-muted-foreground">
                        {{ segment.percent }}%
                    </span>
                </span>
            </li>
        </ul>
    </div>
</template>
