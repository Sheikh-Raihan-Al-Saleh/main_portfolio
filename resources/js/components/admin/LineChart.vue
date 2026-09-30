<script setup lang="ts">
import { computed } from 'vue';
import { useId } from 'vue';

/**
 * A small area/line chart drawn as plain SVG.
 *
 * The admin dashboard needs one trend chart; pulling a charting library for
 * that would cost more than the sixty lines this takes, so the series is
 * mapped to a path here — no dependencies, no runtime beyond the path math,
 * and the hover dots are plain circles with native tooltips via <title>.
 */
type Props = {
    labels: string[];
    series: {
        name: string;
        values: number[];
        color: string;
    }[];
    height?: number;
};

const props = withDefaults(defineProps<Props>(), { height: 180 });

const width = 600;
const pad = { top: 12, right: 8, bottom: 22, left: 28 };

const gradientId = useId();

const maxValue = computed(() => {
    const peak = Math.max(
        1,
        ...props.series.flatMap((serie) => serie.values),
    );

    // Round the axis up to a friendly number.
    const magnitude = 10 ** Math.floor(Math.log10(peak));

    return Math.ceil(peak / magnitude) * magnitude;
});

const plotHeight = computed(() => props.height - pad.top - pad.bottom);

function xFor(index: number, count: number): number {
    if (count <= 1) {
        return pad.left;
    }

    const innerWidth = width - pad.left - pad.right;

    return pad.left + (index / (count - 1)) * innerWidth;
}

function yFor(value: number): number {
    const scale = plotHeight.value / maxValue.value;

    return pad.top + plotHeight.value - value * scale;
}

/** One smooth polyline per series, plus a closed area path beneath it. */
function pathsFor(values: number[]): { line: string; area: string } {
    const count = values.length;

    if (count === 0) {
        return { line: '', area: '' };
    }

    const points = values.map((value, index) => ({
        x: xFor(index, count),
        y: yFor(value),
    }));

    // Straight segments; with 30 points the difference is invisible and the
    // path math stays trivial.
    const line = points
        .map((point, index) => `${index === 0 ? 'M' : 'L'}${point.x},${point.y}`)
        .join(' ');

    const baseline = pad.top + plotHeight.value;
    const first = points[0];
    const last = points[points.length - 1];
    const area = `${line} L${last.x},${baseline} L${first.x},${baseline} Z`;

    return { line, area };
}

const yTicks = computed(() => [0, maxValue.value / 2, maxValue.value]);

const xTickIndexes = computed(() => {
    const count = props.labels.length;

    if (count === 0) {
        return [];
    }

    // Roughly five labels across, always including the last day.
    const step = Math.max(1, Math.floor((count - 1) / 4));
    const indexes: number[] = [];

    for (let index = 0; index < count; index += step) {
        indexes.push(index);
    }

    if (indexes[indexes.length - 1] !== count - 1) {
        indexes.push(count - 1);
    }

    return indexes;
});
</script>

<template>
    <div class="w-full">
        <svg
            :viewBox="`0 0 ${width} ${props.height}`"
            class="w-full"
            role="img"
            :aria-label="`Chart of ${props.series.map((serie) => serie.name).join(' and ')}`"
        >
            <defs>
                <linearGradient
                    v-for="(serie, serieIndex) in props.series"
                    :id="`${gradientId}-fill-${serieIndex}`"
                    :key="serie.name"
                    x1="0"
                    y1="0"
                    x2="0"
                    y2="1"
                >
                    <stop offset="0%" :stop-color="serie.color" stop-opacity="0.25" />
                    <stop offset="100%" :stop-color="serie.color" stop-opacity="0.02" />
                </linearGradient>
            </defs>

            <!-- Horizontal grid + y labels -->
            <g v-for="tick in yTicks" :key="tick">
                <line
                    :x1="pad.left"
                    :x2="width - pad.right"
                    :y1="yFor(tick)"
                    :y2="yFor(tick)"
                    class="stroke-border"
                    stroke-dasharray="3 4"
                    stroke-width="1"
                />
                <text
                    :x="pad.left - 6"
                    :y="yFor(tick) + 3"
                    class="fill-muted-foreground text-[9px]"
                    text-anchor="end"
                >
                    {{ tick }}
                </text>
            </g>

            <!-- Series -->
            <template v-for="(serie, serieIndex) in props.series" :key="serie.name">
                <path
                    :d="pathsFor(serie.values).area"
                    :fill="`url(#${gradientId}-fill-${serieIndex})`"
                />
                <path
                    :d="pathsFor(serie.values).line"
                    fill="none"
                    :stroke="serie.color"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                    stroke-width="2"
                />

                <!-- Hover targets with native tooltips -->
                <circle
                    v-for="(value, index) in serie.values"
                    v-show="value > 0"
                    :key="`${serie.name}-${index}`"
                    :cx="xFor(index, serie.values.length)"
                    :cy="yFor(value)"
                    fill="var(--color-background, white)"
                    :stroke="serie.color"
                    stroke-width="2"
                    r="2.5"
                    class="opacity-0 transition-opacity hover:opacity-100 [&:hover]:opacity-100"
                >
                    <title>{{ `${props.labels[index]}: ${serie.name} ${value}` }}</title>
                </circle>
            </template>

            <!-- X labels -->
            <text
                v-for="index in xTickIndexes"
                :key="`x-${index}`"
                :x="xFor(index, props.labels.length)"
                :y="props.height - 6"
                class="fill-muted-foreground text-[9px]"
                text-anchor="middle"
            >
                {{ props.labels[index] }}
            </text>
        </svg>

        <div class="mt-1 flex items-center justify-center gap-4">
            <span
                v-for="serie in props.series"
                :key="serie.name"
                class="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            >
                <span
                    class="size-2 rounded-full"
                    :style="{ backgroundColor: serie.color }"
                />
                {{ serie.name }}
            </span>
        </div>
    </div>
</template>
