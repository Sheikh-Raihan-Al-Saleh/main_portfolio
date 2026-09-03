<script setup lang="ts">
import { ref } from 'vue';
import { cn } from '@/lib/utils';

type Props = {
    class?: string;
};

const props = withDefaults(defineProps<Props>(), { class: undefined });

const element = ref<HTMLElement | null>(null);

/**
 * Writes the pointer position straight to CSS custom properties rather than
 * reactive state: the glow is decorative, and skipping the render cycle keeps
 * it smooth on cards that contain a lot of markup.
 */
function onPointerMove(event: PointerEvent) {
    const target = element.value;

    if (!target) {
        return;
    }

    const rect = target.getBoundingClientRect();
    target.style.setProperty('--spot-x', `${event.clientX - rect.left}px`);
    target.style.setProperty('--spot-y', `${event.clientY - rect.top}px`);
}
</script>

<template>
    <div
        ref="element"
        :class="cn('spotlight relative overflow-hidden', props.class)"
        @pointermove="onPointerMove"
    >
        <slot />
    </div>
</template>
