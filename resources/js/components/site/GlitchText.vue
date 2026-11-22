<script setup lang="ts">
import { cn } from '@/lib/utils';

/**
 * Text that glitches every few seconds: two offset, colour-split copies
 * flicker through sliced bands for a fraction of a second, like a corrupted
 * terminal frame, then the text settles. Pure CSS; the copies are hidden
 * from assistive tech, and reduced motion keeps the text still.
 */
type Props = {
    text: string;
    as?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    as: 'span',
    class: undefined,
});
</script>

<template>
    <component
        :is="as"
        :class="cn('glitch relative inline-block', props.class)"
        :data-text="text"
    >
        {{ text }}
    </component>
</template>

<style scoped>
.glitch::before,
.glitch::after {
    content: attr(data-text);
    position: absolute;
    inset: 0;
    pointer-events: none;
    opacity: 0;
}

.glitch::before {
    color: #ff2bd6;
    animation: glitch-a 5s steps(1) infinite;
}

.glitch::after {
    color: #19e6ff;
    animation: glitch-b 5s steps(1) infinite;
}

/* Quiet for most of the cycle, then a burst of offset slices. */
@keyframes glitch-a {
    0%,
    86%,
    100% {
        opacity: 0;
        transform: none;
        clip-path: inset(0 0 0 0);
    }
    87% {
        opacity: 0.85;
        transform: translate(-0.04em, 0.02em);
        clip-path: inset(12% 0 62% 0);
    }
    89% {
        opacity: 0.85;
        transform: translate(0.05em, -0.02em);
        clip-path: inset(58% 0 18% 0);
    }
    91% {
        opacity: 0.85;
        transform: translate(-0.03em, 0);
        clip-path: inset(34% 0 44% 0);
    }
    93% {
        opacity: 0;
    }
}

@keyframes glitch-b {
    0%,
    87%,
    100% {
        opacity: 0;
        transform: none;
        clip-path: inset(0 0 0 0);
    }
    88% {
        opacity: 0.85;
        transform: translate(0.05em, 0.01em);
        clip-path: inset(66% 0 8% 0);
    }
    90% {
        opacity: 0.85;
        transform: translate(-0.05em, -0.01em);
        clip-path: inset(6% 0 76% 0);
    }
    92% {
        opacity: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .glitch::before,
    .glitch::after {
        animation: none;
        display: none;
    }
}
</style>
