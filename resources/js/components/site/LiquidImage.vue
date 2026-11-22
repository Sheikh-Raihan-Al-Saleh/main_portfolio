<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { loadLiquid } from '@/composables/useLiquid';
import type { LiquidHandle } from '@/lib/liquid/engine';
import { cn } from '@/lib/utils';

/**
 * An image that turns liquid: it ripples from the pointer on hover and bends
 * with the scroll speed, drawn by the shared WebGL engine.
 *
 * The real <img> is always rendered (server-side, lazy, with its alt text),
 * so it is what search engines, screen readers and visitors without WebGL or
 * with reduced motion get. Once the WebGL copy has drawn its first frame the
 * <img> fades out underneath it and stays in place for accessibility.
 */
type Props = {
    src: string;
    alt?: string;
    class?: string;
    imgClass?: string;
    eager?: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    alt: '',
    class: undefined,
    imgClass: undefined,
    eager: false,
});

const host = ref<HTMLElement | null>(null);
const surface = ref<HTMLCanvasElement | null>(null);
const live = ref(false);

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
        <img
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
        />
        <canvas
            ref="surface"
            data-liquid-surface
            class="pointer-events-none absolute inset-0 size-full transition-opacity duration-500"
            :class="live ? 'opacity-100' : 'opacity-0'"
            aria-hidden="true"
        />
    </div>
</template>
