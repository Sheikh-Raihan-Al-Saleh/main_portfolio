<script setup lang="ts">
import { cn } from '@/lib/utils';

type Props = {
    /** Corner radius of the border ring. Match the inner surface. */
    rounded?: string;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    rounded: 'rounded-2xl',
    class: undefined,
});
</script>

<template>
    <!--
      A rotating conic gradient sits behind the content and is clipped by the
      padded wrapper, leaving only a 1px ring visible. Cheaper and smoother
      than animating a border-image.
    -->
    <div :class="cn('relative p-px', props.rounded, props.class)">
        <div
            :class="
                cn(
                    'animated-border absolute inset-0 overflow-hidden',
                    props.rounded,
                )
            "
            aria-hidden="true"
        />
        <div :class="cn('relative h-full w-full bg-card', props.rounded)">
            <slot />
        </div>
    </div>
</template>

<style scoped>
.animated-border::before {
    content: '';
    position: absolute;
    /* Oversized and centred so the rotating gradient always covers the box. */
    inset: -100%;
    background: conic-gradient(
        from 0deg,
        transparent 0deg,
        var(--brand) 60deg,
        var(--brand-accent) 120deg,
        transparent 200deg,
        transparent 360deg
    );
    animation: border-spin 6s linear infinite;
}

@keyframes border-spin {
    to {
        transform: rotate(360deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .animated-border::before {
        animation: none;
        background: var(--brand);
        opacity: 0.4;
    }
}
</style>
