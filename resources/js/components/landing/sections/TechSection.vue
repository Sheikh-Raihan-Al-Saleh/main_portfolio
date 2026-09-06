<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import MarqueeRow from '@/components/motion/MarqueeRow.vue';
import TechBadge from '@/components/portfolio/TechBadge.vue';
import { inViewOnce, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'tech' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const items = computed(() => props.section.data.items ?? []);
const useMarquee = computed(() => items.value.length >= 8);
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
        wide
        muted
    >
        <motion.div
            v-if="!useMarquee"
            :variants="stagger(0.05)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="flex flex-wrap justify-center gap-2.5"
        >
            <span
                v-for="tech in items"
                :key="tech"
                class="card-3d inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700"
            >
                <span class="size-2 rounded-full bg-[#2563eb]" /> {{ tech }}
            </span>
        </motion.div>
        <MarqueeRow v-else :duration="32">
            <span
                v-for="tech in items"
                :key="tech"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm"
            >
                <span class="size-2 rounded-full bg-[#2563eb]" /> {{ tech }}
            </span>
        </MarqueeRow>
        <div
            v-if="useMarquee"
            class="sr-only flex flex-wrap justify-center gap-2"
        >
            <TechBadge v-for="item in items" :key="item" :label="item" />
        </div>
    </SectionShell>
</template>
