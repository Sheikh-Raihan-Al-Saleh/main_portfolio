<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { loadLiquid } from '@/composables/useLiquid';
import type { LiquidHandle } from '@/lib/liquid/engine';
import { cn } from '@/lib/utils';
import type { LiquidImageProps } from '@/types';

/**
 * An image that turns liquid: it ripples from the pointer on hover and bends
 * with the scroll speed, drawn by the shared WebGL engine.
 *
 * The real <img> is always rendered (server-side, lazy, with its alt text),
 * so it is what search engines, screen readers and visitors without WebGL or
 * with reduced motion get. Once the WebGL copy has drawn its first frame the
 * <img> fades out underneath it and stays in place for accessibility.
 */

const props = withDefaults(defineProps<LiquidImageProps>(), {
    alt: '',
    class: undefined,
    imgClass: undefined,
    eager: false,
});

const host = ref<HTMLElement | null>(null);
const surface = ref<HTMLCanvasElement | null>(null);
const live = ref(false);
// A missing file shows a neutral placeholder instead of a broken image.
const failed = ref(false);

let handle: LiquidHandle | null = null;
let unmounted = false;

onMounted(async () => {
    const engine = await loadLiquid();

    if (!engine || unmounted || !host.value || !surface.value) {
        return;
    }

    handle = engine.attachImage(host.value, surface.value, props.src, () => {
        live.value = true;
    });
});

onBeforeUnmount(() => {
    unmounted = true;
    handle?.destroy();
});
</script>

<template>
    <div
        ref="host"
        :class="cn('relative overflow-hidden', props.class)"
        data-liquid-image
    >
        <div
            v-if="failed"
            class="absolute inset-0 bg-paper-deep"
            role="img"
            :aria-label="alt"
        />
        <img
            v-else
            :src="src"
            :alt="alt"
            :loading="eager ? 'eager' : 'lazy'"
            decoding="async"
            :class="
                cn(
                    'size-full object-cover transition-opacity duration-500',
                    live && 'opacity-0',
                    props.imgClass,
                )
            "
            @error="failed = true"
        />
        <canvas
            ref="surface"
            data-liquid-surface
            class="pointer-events-none absolute inset-0 size-full transition-opacity duration-500"
            :class="live && !failed ? 'opacity-100' : 'opacity-0'"
            aria-hidden="true"
        />
    </div>
</template>
