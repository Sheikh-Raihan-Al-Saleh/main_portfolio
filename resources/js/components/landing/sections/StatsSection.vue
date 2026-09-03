<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import AnimatedCounter from '@/components/portfolio/AnimatedCounter.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
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
        <motion.dl
            :variants="stagger(0.1)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4"
        >
            <motion.div
                v-for="(item, index) in items"
                :key="index"
                :variants="fadeUp"
                class="min-w-0 rounded-lg border border-border p-6 text-center"
            >
                <dd
                    class="font-display text-4xl font-bold break-words text-brand sm:text-5xl"
                >
                    <AnimatedCounter
                        :value="item.value"
                        :suffix="item.suffix ?? ''"
                    />
                </dd>
                <dt class="mt-2 text-sm text-muted-foreground">
                    {{ item.label }}
                </dt>
            </motion.div>
        </motion.dl>
    </SectionShell>
</template>
