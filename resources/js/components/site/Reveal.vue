<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { EASE_OUT, inViewOnce } from '@/lib/motion';

/**
 * Fades and lifts its content in the first time it scrolls into view.
 * Reduced motion is honoured globally by the layout's <MotionConfig>.
 */
type Props = {
    delay?: number;
    y?: number;
};

const props = withDefaults(defineProps<Props>(), {
    delay: 0,
    y: 24,
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
    <motion.div
        initial="hidden"
        while-in-view="visible"
        :in-view-options="inViewOnce"
        :variants="variants"
    >
        <slot />
    </motion.div>
</template>
