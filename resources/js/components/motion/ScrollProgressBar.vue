<script setup lang="ts">
import { motion } from 'motion-v';
import { useScrollProgress } from '@/composables/useScrollProgress';
import { cn } from '@/lib/utils';

type Props = {
    class?: string;
};

const props = withDefaults(defineProps<Props>(), { class: undefined });

// Smoothed: an exact value jitters visibly on trackpads with momentum.
const { progress } = useScrollProgress({ smooth: true });
</script>

<template>
    <!-- Purely decorative: the scrollbar already conveys this to AT. -->
    <div
        :class="
            cn('pointer-events-none h-px w-full overflow-hidden', props.class)
        "
        aria-hidden="true"
    >
        <motion.div
            class="h-full w-full origin-left bg-gradient-to-r from-brand to-brand-accent"
            :style="{ scaleX: progress }"
        />
    </div>
</template>
