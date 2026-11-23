<script setup lang="ts">
import { AnimatePresence, motion } from 'motion-v';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import BrandLogo from '@/components/brand/BrandLogo.vue';
import { usePrefersReducedMotion } from '@/composables/usePrefersReducedMotion';

/**
 * A short brand reveal on the first page load only: the logo mark draws in,
 * then the curtain lifts. Skipped entirely for reduced motion, and never
 * blocks longer than about a second.
 */
const reducedMotion = usePrefersReducedMotion();
const visible = ref(true);
// The timer that lifts the curtain, cleared if the loader unmounts first.
let hideTimerId: number | null = null;

onMounted(() => {
    const seen = sessionStorage.getItem('site-loader-seen') === '1';

    if (reducedMotion.value || seen) {
        visible.value = false;

        return;
    }

    try {
        sessionStorage.setItem('site-loader-seen', '1');
    } catch {
        // Storage may be blocked; the loader still finishes.
    }

    hideTimerId = window.setTimeout(() => (visible.value = false), 1100);
});

onBeforeUnmount(() => {
    if (hideTimerId !== null) {
        window.clearTimeout(hideTimerId);
    }
});
</script>

<template>
    <AnimatePresence>
        <motion.div
            v-if="visible"
            class="site-loader"
            data-testid="site-loader"
            :initial="{ y: 0 }"
            :exit="{ y: '-100%' }"
            :transition="{ duration: 0.8, ease: [0.76, 0, 0.24, 1] }"
            aria-hidden="true"
        >
            <motion.div
                :initial="{ opacity: 0, scale: 0.85, filter: 'blur(8px)' }"
                :animate="{ opacity: 1, scale: 1, filter: 'blur(0px)' }"
                :transition="{ duration: 0.7, ease: [0.16, 1, 0.3, 1] }"
            >
                <BrandLogo variant="mark" size="xl" />
            </motion.div>
        </motion.div>
    </AnimatePresence>
</template>
