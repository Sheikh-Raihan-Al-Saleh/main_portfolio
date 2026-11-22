<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * An editorial cursor: a small dot that tracks the pointer exactly and a
 * ring that trails it, growing over links and buttons and turning into a
 * "View" disc over anything marked data-cursor="view" (project images).
 *
 * The system cursor stays visible, so nothing is lost for anyone. Only on
 * devices with a fine pointer, and not under reduced motion.
 */
const enabled = ref(false);
const visible = ref(false);
const mode = ref<'default' | 'link' | 'view'>('default');

const dot = ref<HTMLElement | null>(null);
const ring = ref<HTMLElement | null>(null);

const target = { x: -100, y: -100 };
const trail = { x: -100, y: -100 };
let frame: number | null = null;

function loop() {
    trail.x += (target.x - trail.x) * 0.18;
    trail.y += (target.y - trail.y) * 0.18;

    if (dot.value) {
        dot.value.style.transform = `translate3d(${target.x}px, ${target.y}px, 0)`;
    }

    if (ring.value) {
        ring.value.style.transform = `translate3d(${trail.x}px, ${trail.y}px, 0)`;
    }

    const settled =
        Math.abs(target.x - trail.x) < 0.1 &&
        Math.abs(target.y - trail.y) < 0.1;
    frame = settled ? null : requestAnimationFrame(loop);
}

function onMove(event: PointerEvent) {
    if (event.pointerType !== 'mouse') {
        return;
    }

    target.x = event.clientX;
    target.y = event.clientY;
    visible.value = true;

    const el = event.target as Element | null;

    if (el?.closest?.('[data-cursor="view"]')) {
        mode.value = 'view';
    } else if (
        el?.closest?.(
            'a, button, [role="button"], input, textarea, select, label',
        )
    ) {
        mode.value = 'link';
    } else {
        mode.value = 'default';
    }

    if (frame === null) {
        frame = requestAnimationFrame(loop);
    }
}

function onLeave() {
    visible.value = false;
}

onMounted(() => {
    enabled.value =
        window.matchMedia('(pointer: fine)').matches &&
        !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!enabled.value) {
        return;
    }

    window.addEventListener('pointermove', onMove, { passive: true });
    document.documentElement.addEventListener('pointerleave', onLeave);
});

onBeforeUnmount(() => {
    window.removeEventListener('pointermove', onMove);
    document.documentElement.removeEventListener('pointerleave', onLeave);

    if (frame !== null) {
        cancelAnimationFrame(frame);
    }
});
</script>

<template>
    <div
        v-if="enabled"
        class="pointer-events-none fixed inset-0 z-[150] transition-opacity duration-300"
        :class="visible ? 'opacity-100' : 'opacity-0'"
        aria-hidden="true"
        data-testid="custom-cursor"
    >
        <div ref="dot" class="absolute top-0 left-0">
            <span
                class="block size-1.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-[var(--accent-fill)] mix-blend-difference"
            />
        </div>
        <div ref="ring" class="absolute top-0 left-0">
            <span
                class="grid -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border transition-all duration-300 ease-out"
                :class="{
                    'size-9 border-foreground/40': mode === 'default',
                    'size-14 border-[var(--accent-fill)] bg-[var(--accent-fill)]/10':
                        mode === 'link',
                    'size-24 border-transparent bg-[var(--accent-fill)] text-[var(--accent-ink)]':
                        mode === 'view',
                }"
            >
                <span
                    class="font-mono text-[11px] font-semibold tracking-widest uppercase transition-opacity duration-200"
                    :class="mode === 'view' ? 'opacity-100' : 'opacity-0'"
                    >View</span
                >
            </span>
        </div>
    </div>
</template>
