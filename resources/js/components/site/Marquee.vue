<script setup lang="ts">
/**
 * An endless horizontal ticker. The slot is rendered twice so the loop is
 * seamless; the copy is hidden from assistive technology and inert. Pauses on hover
 * and stops for visitors who prefer reduced motion (site.css).
 */
withDefaults(
    defineProps<{
        /** Seconds for one full loop. */
        duration?: number;
        gap?: string;
        reverse?: boolean;
    }>(),
    {
        duration: 40,
        gap: '3rem',
        reverse: false,
    },
);
</script>

<template>
    <div
        class="marquee"
        :style="{ '--marquee-duration': `${duration}s`, '--marquee-gap': gap }"
    >
        <div
            class="marquee-track"
            :style="reverse ? { animationDirection: 'reverse' } : undefined"
        >
            <slot />
        </div>
        <!-- The visual copy for the seamless loop: hidden and inert, so screen
             readers and the keyboard only meet each item once. -->
        <div
            class="marquee-track"
            :style="reverse ? { animationDirection: 'reverse' } : undefined"
            aria-hidden="true"
            inert
        >
            <slot />
        </div>
    </div>
</template>
