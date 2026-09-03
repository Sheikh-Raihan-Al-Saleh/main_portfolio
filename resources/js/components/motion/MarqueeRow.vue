<script setup lang="ts">
import { computed } from 'vue';
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion';
import { cn } from '@/lib/utils';

type Props = {
    /** Seconds for one full pass. Larger is slower. */
    duration?: number;
    reverse?: boolean;
    class?: string;
};

const props = withDefaults(defineProps<Props>(), {
    duration: 40,
    reverse: false,
    class: undefined,
});

const reduced = usePrefersReducedMotion();

const style = computed(() => ({
    '--marquee-duration': `${props.duration}s`,
    '--marquee-direction': props.reverse ? 'reverse' : 'normal',
}));
</script>

<template>
    <!--
      Two identical tracks scroll as one; when the first has moved exactly its
      own width, the second sits where it started, so the loop is seamless.
      Under reduced motion the track simply becomes a horizontal scroller.
    -->
    <div
        :class="
            cn('mask-fade-x group relative flex overflow-hidden', props.class)
        "
        :style="style"
    >
        <div
            :class="
                cn(
                    'flex w-max shrink-0 items-center gap-4',
                    reduced
                        ? 'overflow-x-auto'
                        : 'marquee-track group-hover:[animation-play-state:paused]',
                )
            "
        >
            <slot />
        </div>

        <div
            v-if="!reduced"
            class="marquee-track flex w-max shrink-0 items-center gap-4 group-hover:[animation-play-state:paused]"
            aria-hidden="true"
        >
            <slot />
        </div>
    </div>
</template>

<style scoped>
.marquee-track {
    padding-right: 1rem;
    animation: marquee-scroll var(--marquee-duration, 40s) linear infinite;
    animation-direction: var(--marquee-direction, normal);
}

@keyframes marquee-scroll {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(-100%);
    }
}

@media (prefers-reduced-motion: reduce) {
    .marquee-track {
        animation: none;
    }
}
</style>
