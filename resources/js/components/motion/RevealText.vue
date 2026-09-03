<script setup lang="ts">
import { motion } from 'motion-v';
import { inViewOnce, maskReveal } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    as?: string;
    /** Delay before the wipe begins, in seconds. */
    delay?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    as: 'div',
    delay: 0,
    class: undefined,
});
</script>

<template>
    <!-- The clipping wrapper is what makes this a wipe rather than a slide:
         the inner line starts fully below the mask edge. -->
    <component :is="props.as" :class="cn('overflow-hidden', props.class)">
        <motion.span
            class="block will-change-transform"
            :variants="maskReveal"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            :transition="{ delay: props.delay }"
        >
            <slot />
        </motion.span>
    </component>
</template>
