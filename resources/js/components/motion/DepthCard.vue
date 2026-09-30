<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { useTilt } from '@/composables/useTilt';
import { EASE_OUT, inViewOnce, perspectiveCard, SPRING_SOFT } from '@/lib/motion';
import { cn } from '@/lib/utils';

/**
 * A card that lives in 3D space.
 *
 * It does two things `TiltCard` deliberately does not: it arrives *from depth*
 * on scroll (rotated and pushed back, then settling forward) and it lifts
 * toward the viewer on hover. Tilt is layered on top and can be switched off
 * for cards whose contents should not move, such as form fields.
 *
 * It also publishes the pointer position it is already tracking as `--spot-x`
 * / `--spot-y` custom properties, which a card surface can paint a sheen from
 * (see `.studio-panel`) without any further scripting.
 */
type Props = {
    /** Degrees of X-axis rotation the card starts from. */
    depth?: number;
    /** Rise distance on the hidden frame, in pixels. */
    rise?: number;
    /** Pointer tilt. Off for anything interactive inside the card. */
    tilt?: boolean;
    maxTilt?: number;
    /** Scale while hovered. */
    hoverScale?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    depth: 8,
    rise: 44,
    tilt: true,
    maxTilt: 6,
    hoverScale: 1.015,
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
} = useTilt({ maxTilt: props.maxTilt, hoverScale: props.hoverScale });

const variants = computed(() => ({
    ...perspectiveCard,
    hidden: {
        ...perspectiveCard.hidden,
        y: props.rise,
        rotateX: props.depth,
    },
    visible: {
        ...perspectiveCard.visible,
        transition: { duration: 0.8, ease: EASE_OUT },
    },
}));
</script>

<template>
    <!-- Perspective on the wrapper so the card rotates relative to the viewer
         rather than flattening against its own container. -->
    <div class="[perspective:1400px]">
        <motion.div
            :variants="variants"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="h-full"
        >
            <motion.div
                ref="elementRef"
                :class="
                    cn(
                        'relative h-full [transform-style:preserve-3d]',
                        props.class,
                    )
                "
                :style="glareStyle"
                :animate="props.tilt ? { rotateX, rotateY, scale } : undefined"
                :transition="SPRING_SOFT"
                @pointermove="onPointerMove"
                @pointerenter="onPointerEnter"
                @pointerleave="onPointerLeave"
            >
                <slot />
            </motion.div>
        </motion.div>
    </div>
</template>
