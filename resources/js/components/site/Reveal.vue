<script setup lang="ts">
import { Motion } from 'motion-v';
import { computed } from 'vue';
import { EASE_OUT, inViewOnce } from '@/lib/motion';

/**
 * Fades its content up into place the first time it scrolls into view.
 * `as` sets the element, so a revealed item can be a real <li> inside a list.
 */
type Props = {
    delay?: number;
    y?: number;
    as?: 'div' | 'li' | 'section' | 'article' | 'span';
};

const props = withDefaults(defineProps<Props>(), {
    delay: 0,
    y: 24,
    as: 'div',
});

const variants = computed(() => ({
    hidden: { opacity: 0, y: props.y },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.8, ease: EASE_OUT, delay: props.delay },
    },
}));
</script>

<template>
    <Motion
        :as="as"
        initial="hidden"
        while-in-view="visible"
        :in-view-options="inViewOnce"
        :variants="variants"
    >
        <slot />
    </Motion>
</template>
