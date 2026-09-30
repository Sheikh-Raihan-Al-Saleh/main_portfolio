<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import { EASE_OUT } from '@/lib/motion';
import { cn } from '@/lib/utils';

/**
 * The Studio's single entrance primitive.
 *
 * Every section-level element that animates in on scroll goes through this
 * component, so entrances stay consistent and tunable in one place instead of
 * being re-implemented per section. It is deliberately dumb: one wrapper, one
 * `whileInView` variant pair, transform/opacity only (both compositor-friendly
 * properties), fired once.
 */
type Props = {
    /** Where the element travels *from*. */
    direction?: 'up' | 'down' | 'left' | 'right' | 'none';
    /** Travel distance in pixels. Ignored when `direction` is 'none'. */
    distance?: number;
    /** Defocus amount on the hidden frame. 0 keeps the rasterisation cheap. */
    blur?: number;
    /** Start scale on the hidden frame (1 disables the scale). */
    scale?: number;
    /** Seconds of delay, for hand-timed sequences. */
    delay?: number;
    duration?: number;
    /** Only animate the first time it enters the viewport. */
    once?: boolean;
    /** Fraction of the element that must be visible before firing. */
    amount?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    direction: 'up',
    distance: 32,
    blur: 0,
    scale: 1,
    delay: 0,
    duration: 0.7,
    once: true,
    amount: 0.25,
    class: undefined,
});

const variants = computed(() => {
    const hidden: Record<string, number | string> = { opacity: 0 };

    if (props.direction === 'up') {
        hidden.y = props.distance;
    } else if (props.direction === 'down') {
        hidden.y = -props.distance;
    } else if (props.direction === 'left') {
        hidden.x = props.distance;
    } else if (props.direction === 'right') {
        hidden.x = -props.distance;
    }

    if (props.blur > 0) {
        hidden.filter = `blur(${props.blur}px)`;
    }

    if (props.scale !== 1) {
        hidden.scale = props.scale;
    }

    return {
        hidden,
        visible: {
            opacity: 1,
            x: 0,
            y: 0,
            scale: 1,
            filter: 'blur(0px)',
            transition: {
                duration: props.duration,
                delay: props.delay,
                ease: EASE_OUT,
            },
        },
    };
});

/**
 * `margin` pulls the trigger a little before the element is fully in frame, so
 * the reveal finishes about when the reader's eye arrives.
 */
const inViewOptions = computed(
    () =>
        ({
            once: props.once,
            amount: props.amount,
            margin: '0px 0px -60px 0px',
            // `as const` keeps the margin a string literal, which is what
            // motion's `InViewOptions.margin` union type accepts.
        }) as const,
);
</script>

<template>
    <motion.div
        :class="cn(props.class)"
        :variants="variants"
        initial="hidden"
        while-in-view="visible"
        :in-view-options="inViewOptions"
    >
        <slot />
    </motion.div>
</template>
