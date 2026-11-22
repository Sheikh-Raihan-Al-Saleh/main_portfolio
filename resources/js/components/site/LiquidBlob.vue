<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { loadLiquid } from '@/composables/useLiquid';
import type { LiquidHandle } from '@/lib/liquid/engine';
import { cn } from '@/lib/utils';

/**
 * A flowing liquid shape in the face's accent colour, for heroes. Drawn by
 * the shared WebGL engine; until then, and for visitors without WebGL or with
 * reduced motion, a soft CSS gradient in the same colour holds its place.
 */
type Props = {
    class?: string;
};

const props = withDefaults(defineProps<Props>(), { class: undefined });

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

    handle = engine.attachBlob(host.value, surface.value, () => {
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
        :class="cn('pointer-events-auto relative', props.class)"
        data-liquid-blob
        aria-hidden="true"
    >
        <div
            class="absolute inset-[12%] rounded-full bg-[radial-gradient(circle_at_40%_40%,var(--scene-1),var(--scene-2)_45%,transparent_70%)] opacity-80 blur-2xl transition-opacity duration-700"
            :class="live ? 'opacity-0' : ''"
        />
        <canvas
            ref="surface"
            data-liquid-surface
            class="absolute inset-0 size-full transition-opacity duration-700"
            :class="live ? 'opacity-100' : 'opacity-0'"
        />
    </div>
</template>
