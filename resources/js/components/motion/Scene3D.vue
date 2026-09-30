<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A CSS 3D scene.
 *
 * Gives its slot a real perspective space (`perspective` + `preserve-3d`) and
 * leans that space toward the pointer, so children placed at different Z
 * depths move by different amounts and the composition reads as a physical
 * object rather than a flat picture.
 *
 * The lean is written as two custom properties on the scene element from a
 * single rAF callback per pointer move — no reactive state, therefore no
 * component re-render while the cursor travels. Everything is disabled for
 * coarse pointers, narrow viewports and reduced motion, which leaves the
 * scene perfectly still (and still correct).
 */
type Props = {
    /** Degrees of lean at the edge of the scene box. */
    maxRotate?: number;
    /** `perspective` distance in pixels — smaller is a more extreme lens. */
    perspective?: number;
    /** Adds the ambient brand glow behind the scene. */
    glow?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    maxRotate: 9,
    perspective: 1200,
    glow: false,
    class: undefined,
});

const scene = ref<HTMLElement | null>(null);

const target = { x: 0, y: 0 };
let frame: number | null = null;
let enabled = false;
let query: MediaQueryList | null = null;

function flush() {
    frame = null;

    const element = scene.value;

    if (!element) {
        return;
    }

    element.style.setProperty('--scene-x', `${target.x.toFixed(2)}deg`);
    element.style.setProperty('--scene-y', `${target.y.toFixed(2)}deg`);
}

function schedule() {
    if (frame === null) {
        frame = requestAnimationFrame(flush);
    }
}

function onPointerMove(event: PointerEvent) {
    const element = scene.value;

    if (!enabled || !element) {
        return;
    }

    const rect = element.getBoundingClientRect();

    if (!rect.width || !rect.height) {
        return;
    }

    const x = (event.clientX - (rect.left + rect.width / 2)) / rect.width;
    const y = (event.clientY - (rect.top + rect.height / 2)) / rect.height;

    target.x = Math.max(-1, Math.min(1, x)) * props.maxRotate;
    target.y = Math.max(-1, Math.min(1, y)) * -props.maxRotate;

    schedule();
}

function onPointerLeave() {
    target.x = 0;
    target.y = 0;

    if (enabled) {
        schedule();
    }
}

/**
 * Re-evaluated on every media change rather than only at mount, so rotating a
 * tablet or resizing a window switches the lean on and off correctly.
 */
function sync() {
    enabled = Boolean(query?.matches);
}

onMounted(() => {
    query = window.matchMedia(
        '(prefers-reduced-motion: no-preference) and (pointer: fine) and (min-width: 1024px)',
    );
    sync();
    query.addEventListener('change', sync);
});

onBeforeUnmount(() => {
    query?.removeEventListener('change', sync);
    query = null;

    if (frame !== null) {
        cancelAnimationFrame(frame);
    }

    frame = null;
});
</script>

<template>
    <div
        ref="scene"
        :class="cn('relative', props.class)"
        :style="{ perspective: `${props.perspective}px` }"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <div
            v-if="props.glow"
            aria-hidden="true"
            class="pointer-events-none absolute inset-0 opacity-70"
            style="
                background: radial-gradient(
                    50% 50% at 50% 45%,
                    var(--brand-glow),
                    transparent 70%
                );
            "
        />

        <div class="scene-3d-inner relative [transform-style:preserve-3d]">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.scene-3d-inner {
    transform: rotateY(var(--scene-x, 0deg)) rotateX(var(--scene-y, 0deg));
    transform-origin: center;
    transition: transform 900ms cubic-bezier(0.16, 1, 0.3, 1);
    transform-style: preserve-3d;
}
</style>
