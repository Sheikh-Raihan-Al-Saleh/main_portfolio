<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { useTilt } from '@/composables/useTilt';
import { SPRING_SOFT } from '@/lib/motion';
import { cn } from '@/lib/utils';

/**
 * A laptop, tablet or phone with a real screen in it.
 *
 * Used where the Studio wants to show the same product on more than one
 * surface, or show a product at all before a cover image exists. Reuses the
 * existing device frames from the design system (`.device-laptop-frame`,
 * `.device-phone-frame`, …) so it matches the rest of the site, and adds the
 * part those classes cannot express: pointer tilt, so the device sits in the
 * scene rather than on top of it.
 */
type Props = {
    device?: 'laptop' | 'tablet' | 'phone';
    imageUrl?: string | null;
    alt?: string;
    /** Tilt toward the pointer. */
    tilt?: boolean;
    maxTilt?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    device: 'laptop',
    imageUrl: null,
    alt: '',
    tilt: true,
    maxTilt: 7,
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
} = useTilt({ maxTilt: props.maxTilt, hoverScale: 1.01 });

const frameClass = computed(() => {
    switch (props.device) {
        case 'phone':
            return 'device-phone-frame';
        case 'tablet':
            return 'device-tablet-frame';
        default:
            return 'device-laptop-frame';
    }
});

const screenClass = computed(() =>
    props.device === 'phone'
        ? 'device-screen device-screen-phone'
        : 'device-screen',
);

const screenAspect = computed(() => {
    switch (props.device) {
        case 'phone':
            return 'aspect-9/19';
        case 'tablet':
            return 'aspect-4/3';
        default:
            return 'aspect-16/10';
    }
});
</script>

<template>
    <!-- Perspective on the wrapper: without it the rotation reads as a skew. -->
    <div class="[perspective:1600px]">
        <motion.div
            ref="elementRef"
            :class="cn('device-shell', props.class)"
            :style="glareStyle"
            :animate="
                props.tilt ? { rotateX, rotateY, scale } : undefined
            "
            :transition="SPRING_SOFT"
            @pointermove="onPointerMove"
            @pointerenter="onPointerEnter"
            @pointerleave="onPointerLeave"
        >
            <div :class="frameClass">
                <div :class="[screenClass, 'bg-muted']">
                    <img
                        v-if="props.imageUrl"
                        :src="props.imageUrl"
                        :alt="props.alt"
                        loading="lazy"
                        decoding="async"
                        :class="cn(
                            'size-full object-cover object-top',
                            screenAspect,
                        )"
                    />
                    <slot v-else name="screen">
                        <div :class="cn('bg-mesh w-full', screenAspect)" />
                    </slot>
                </div>

                <div v-if="props.device === 'laptop'" class="device-laptop-base" />
            </div>
        </motion.div>
    </div>
</template>
