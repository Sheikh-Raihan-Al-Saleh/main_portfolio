<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Matrix-style code rain: columns of falling glyphs (katakana mixed with
 * code symbols) with a bright head and a fading trail. Columns near the
 * pointer fall faster and glow brighter, so the rain reacts to the visitor.
 *
 * A plain 2D canvas at ~30 fps, capped at 1.5x pixel ratio, paused while off
 * screen or in a hidden tab. Under reduced motion it paints one still,
 * sparse frame instead of animating. Colours come from --rain-head and
 * --rain-tail on the element (falling back to the face's accent).
 */
type Props = {
    /** Glyph size in CSS pixels. */
    size?: number;
};

const props = withDefaults(defineProps<Props>(), { size: 16 });

const canvas = ref<HTMLCanvasElement | null>(null);

const GLYPHS =
    'アイウエオカキクケコサシスセソタチツテトナニヌネノ0123456789{}[]<>/=;:+*#$&λ→';

let context: CanvasRenderingContext2D | null = null;
let columns: { y: number; speed: number }[] = [];
let frame: number | null = null;
let last = 0;
let visible = true;
let resizeObserver: ResizeObserver | null = null;
let intersection: IntersectionObserver | null = null;
let head = '#ffffff';
let tail = '#2b4bff';
let dpr = 1;
const pointer = { x: -9999, y: -9999 };

function glyph(): string {
    return GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
}

function readColors() {
    const el = canvas.value;

    if (!el) {
        return;
    }

    const style = getComputedStyle(el);
    head = style.getPropertyValue('--rain-head').trim() || '#ffffff';
    tail =
        style.getPropertyValue('--rain-tail').trim() ||
        style.getPropertyValue('--accent-fill').trim() ||
        '#2b4bff';
}

function resize() {
    const el = canvas.value;

    if (!el || !context) {
        return;
    }

    dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    const { clientWidth, clientHeight } = el;
    el.width = Math.round(clientWidth * dpr);
    el.height = Math.round(clientHeight * dpr);
    context.setTransform(dpr, 0, 0, dpr, 0, 0);
    context.font = `${props.size}px "JetBrains Mono", ui-monospace, monospace`;
    context.textBaseline = 'top';

    const count = Math.ceil(clientWidth / props.size);
    columns = Array.from(
        { length: count },
        (_, i) =>
            columns[i] ?? {
                y: Math.random() * clientHeight * 0.8 - clientHeight * 0.3,
                speed: 0.6 + Math.random() * 0.9,
            },
    );
}

/** One step of the rain: fade the old frame, then draw each column's head. */
function step(still = false) {
    const el = canvas.value;

    if (!el || !context) {
        return;
    }

    const width = el.clientWidth;
    const height = el.clientHeight;
    const s = props.size;

    // Fading the previous frame toward transparent leaves the trails.
    context.globalCompositeOperation = 'destination-out';
    context.fillStyle = still ? 'rgba(0,0,0,1)' : 'rgba(0,0,0,0.12)';
    context.fillRect(0, 0, width, height);
    context.globalCompositeOperation = 'source-over';

    columns.forEach((column, i) => {
        const x = i * s;

        if (still) {
            // A sparse, frozen field: a few short trails per column.
            for (let y = 0; y < height; y += s) {
                if (Math.random() < 0.08) {
                    context!.globalAlpha = 0.15 + Math.random() * 0.35;
                    context!.fillStyle = tail;
                    context!.fillText(glyph(), x, y);
                }
            }

            context!.globalAlpha = 1;

            return;
        }

        // Columns near the pointer speed up and glow.
        const near = Math.hypot(x - pointer.x, column.y - pointer.y) < 140;
        const boost = near ? 2.4 : 1;

        context!.fillStyle = tail;
        context!.globalAlpha = near ? 1 : 0.75;
        context!.fillText(glyph(), x, column.y - s);

        context!.fillStyle = head;
        context!.globalAlpha = 1;
        context!.fillText(glyph(), x, column.y);

        column.y += s * 0.5 * column.speed * boost;

        if (column.y > height + s * 4 && Math.random() > 0.9) {
            column.y = -s * (2 + Math.random() * 20);
            column.speed = 0.6 + Math.random() * 0.9;
        }
    });

    context.globalAlpha = 1;
}

function tick(now: number) {
    frame = requestAnimationFrame(tick);

    // ~30 fps is plenty for falling glyphs and halves the work.
    if (!visible || document.hidden || now - last < 33) {
        return;
    }

    last = now;
    step();
}

function onPointerMove(event: PointerEvent) {
    const rect = canvas.value?.getBoundingClientRect();

    if (rect) {
        pointer.x = event.clientX - rect.left;
        pointer.y = event.clientY - rect.top;
    }
}

onMounted(() => {
    const el = canvas.value;

    if (!el) {
        return;
    }

    context = el.getContext('2d');

    if (!context) {
        return;
    }

    readColors();
    resize();

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        step(true);

        return;
    }

    resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(el);
    intersection = new IntersectionObserver(([entry]) => {
        visible = entry?.isIntersecting ?? true;
    });
    intersection.observe(el);
    window.addEventListener('pointermove', onPointerMove, { passive: true });
    frame = requestAnimationFrame(tick);
});

onBeforeUnmount(() => {
    if (frame !== null) {
        cancelAnimationFrame(frame);
    }

    resizeObserver?.disconnect();
    intersection?.disconnect();
    window.removeEventListener('pointermove', onPointerMove);
});
</script>

<template>
    <canvas
        ref="canvas"
        class="size-full"
        aria-hidden="true"
        data-testid="code-rain"
    />
</template>
