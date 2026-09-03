<script setup lang="ts">
import { motion } from 'motion-v';
import { useMagnetic } from '@/composables/useMagnetic';
import { SPRING_SOFT } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    /** Maximum pull toward the cursor, as a fraction of the pointer offset. */
    strength?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    strength: 0.3,
    class: undefined,
});

const { elementRef, offset, onPointerMove, onPointerLeave } = useMagnetic({
    strength: props.strength,
});
</script>

<template>
    <!--
      A wrapper rather than a button: the caller supplies the real interactive
      element in the slot, so semantics, focus behaviour and disabled state
      stay with whatever they pass in (usually <Button as-child>).
    -->
    <motion.div
        ref="elementRef"
        :class="cn('inline-flex', props.class)"
        :animate="{ x: offset.x, y: offset.y }"
        :transition="SPRING_SOFT"
        @pointermove="onPointerMove"
        @pointerleave="onPointerLeave"
    >
        <slot />
    </motion.div>
</template>
