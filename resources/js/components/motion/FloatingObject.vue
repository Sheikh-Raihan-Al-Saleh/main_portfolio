<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

/**
 * A decorative object adrift in the scene.
 *
 * Transform-only CSS animation (`translate3d` + a couple of degrees of tilt),
 * so the compositor owns it and no JavaScript runs per frame. The Z offset is
 * what makes it read as *in front of* or *behind* whatever shares the scene,
 * so it needs a parent with `preserve-3d` (see `Scene3D`).
 *
 * Reduced motion switches the drift off at the stylesheet level, leaving the
 * object parked at its resting transform.
 */
type Props = {
    /** Vertical travel, in pixels. */
    amplitude?: number;
    /** Seconds for one full drift cycle. */
    duration?: number;
    /** Negative values phase-shift the loop so objects do not move in lockstep. */
    delay?: number;
    /** Degrees of tilt at the top of the cycle. */
    rotate?: number;
    /** Static depth offset in pixels. */
    z?: number;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    amplitude: 14,
    duration: 7,
    delay: 0,
    rotate: 2,
    z: 0,
    class: undefined,
});

const style = computed(() => ({
    '--float-amplitude': `${props.amplitude}px`,
    '--float-rotate': `${props.rotate}deg`,
    '--float-z': `${props.z}px`,
    '--float-duration': `${props.duration}s`,
    '--float-delay': `${props.delay}s`,
}));
</script>

<template>
    <div
        :class="cn('floating-object', props.class)"
        :style="style"
        aria-hidden="true"
    >
        <slot />
    </div>
</template>

<style scoped>
.floating-object {
    transform-style: preserve-3d;
    animation: floating-object var(--float-duration, 7s) ease-in-out
        var(--float-delay, 0s) infinite;
    will-change: transform;
}

@keyframes floating-object {
    0%,
    100% {
        transform: translate3d(0, 0, var(--float-z, 0)) rotate(0deg);
    }
    50% {
        transform: translate3d(
                0,
                calc(var(--float-amplitude, 14px) * -1),
                calc(var(--float-z, 0px) + 10px)
            )
            rotate(var(--float-rotate, 2deg));
    }
}

@media (prefers-reduced-motion: reduce) {
    .floating-object {
        animation: none;
        transform: translate3d(0, 0, var(--float-z, 0));
    }
}
</style>
