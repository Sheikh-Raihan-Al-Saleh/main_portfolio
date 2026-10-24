<script setup lang="ts">
import { useId } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A monitoring widget: a status line and a sparkline that draws itself and
 * then keeps a live dot pulsing at its head. Decorative — it describes the
 * kind of software the studio runs, not a live metric.
 */
type Props = {
    label?: string;
    value?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    label: 'Response time',
    value: 'p95 · healthy',
    class: undefined,
});

const uid = useId();

const points = [6, 14, 10, 18, 12, 20, 15, 24, 18, 22, 16, 26, 21, 28];
const path = points
    .map(
        (y, i) =>
            `${i === 0 ? 'M' : 'L'}${(i / (points.length - 1)) * 160} ${36 - y}`,
    )
    .join(' ');
const last = { x: 160, y: 36 - points[points.length - 1]! };
</script>

<template>
    <div
        :class="
            cn(
                'ui-glass rounded-2xl px-4 py-3 shadow-[var(--shadow-soft)]',
                props.class,
            )
        "
    >
        <div class="flex items-center justify-between gap-6">
            <p class="text-xs text-muted-foreground">{{ label }}</p>
            <p class="inline-flex items-center gap-1.5 text-xs font-semibold">
                <span class="ui-pulse-dot" />
                {{ value }}
            </p>
        </div>
        <svg viewBox="-4 0 172 40" class="mt-2 h-10 w-44" aria-hidden="true">
            <defs>
                <linearGradient :id="`${uid}-fill`" x1="0" x2="0" y1="0" y2="1">
                    <stop
                        offset="0"
                        stop-color="var(--grad-from)"
                        stop-opacity="0.28"
                    />
                    <stop
                        offset="1"
                        stop-color="var(--grad-from)"
                        stop-opacity="0"
                    />
                </linearGradient>
            </defs>
            <path
                :d="`${path} L160 40 L0 40 Z`"
                :fill="`url(#${uid}-fill)`"
                class="ui-spark-fill"
            />
            <path
                :d="path"
                fill="none"
                stroke="var(--grad-from)"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                pathLength="100"
                class="ui-spark-line"
            />
            <circle
                :cx="last.x"
                :cy="last.y"
                r="3.5"
                fill="var(--grad-from)"
                class="ui-spark-dot"
            />
        </svg>
    </div>
</template>
