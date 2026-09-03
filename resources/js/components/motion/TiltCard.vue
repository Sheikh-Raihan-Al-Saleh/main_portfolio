<script setup lang="ts">
import { motion } from 'motion-v';
import { useTilt } from '@/composables/useTilt';
import { SPRING_SOFT } from '@/lib/motion';
import { cn } from '@/lib/utils';

type Props = {
    /** Maximum rotation on each axis, in degrees. */
    maxTilt?: number;
    /** Add the cursor-following glow from the `.spotlight` utility. */
    spotlight?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    maxTilt: 7,
    spotlight: true,
    class: undefined,
});

const {
    elementRef,
    rotateX,
    rotateY,
    scale,
    glareStyle,
    onPointerMove,
    onPointerEnter,
    onPointerLeave,
} = useTilt({ maxTilt: props.maxTilt });
</script>

<template>
    <!-- The perspective lives on the wrapper so the child rotates in 3D
         relative to the viewer rather than flattening. -->
    <div class="[perspective:1200px]">
        <motion.div
            ref="elementRef"
            :class="
                cn(
                    'relative overflow-hidden [transform-style:preserve-3d]',
                    props.spotlight && 'spotlight',
                    props.class,
                )
            "
            :style="glareStyle"
            :animate="{ rotateX, rotateY, scale }"
            :transition="SPRING_SOFT"
            @pointermove="onPointerMove"
            @pointerenter="onPointerEnter"
            @pointerleave="onPointerLeave"
        >
            <slot />
        </motion.div>
    </div>
</template>
