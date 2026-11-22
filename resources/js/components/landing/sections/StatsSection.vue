<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { inViewOnce, perspectiveCard, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'stats' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const items = computed(() => props.section.data.items ?? []);
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
        muted
    >
        <!--
            A list rather than <dl>: each figure sits inside 3D tilt wrappers,
            and <dl> only allows <dt>/<dd> directly or one <div> deep.
        -->
        <motion.div
            role="list"
            :variants="stagger(0.08)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-5 [perspective:1200px] sm:grid-cols-2 lg:grid-cols-4"
        >
            <motion.div
                v-for="(item, index) in items"
                :key="index"
                :variants="perspectiveCard"
                role="listitem"
                class="[transform-style:preserve-3d]"
            >
                <div class="card-3d relative overflow-hidden p-7 text-center">
                    <div
                        class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-gradient-to-br from-signal/15 to-transparent blur-2xl"
                    />
                    <p
                        class="bg-gradient-to-br from-foreground to-muted-foreground bg-clip-text text-4xl font-extrabold text-transparent tabular-nums sm:text-5xl"
                    >
                        <AnimatedCounter
                            :value="item.value"
                            :suffix="item.suffix ?? ''"
                        />
                    </p>
                    <p class="mt-2 text-sm font-medium text-muted-foreground">
                        {{ item.label }}
                    </p>
                    <div
                        class="mx-auto mt-3 h-1 w-8 rounded-full bg-gradient-to-r from-signal to-signal opacity-90"
                    />
                </div>
            </motion.div>
        </motion.div>
    </SectionShell>
</template>
