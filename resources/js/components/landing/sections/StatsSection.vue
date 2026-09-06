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
        <motion.dl
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
                class="[transform-style:preserve-3d]"
            >
                <div class="card-3d relative overflow-hidden p-7 text-center">
                    <div
                        class="pointer-events-none absolute -top-10 -right-10 size-24 rounded-full bg-gradient-to-br from-[#2563eb]/10 to-[#7c3aed]/10 blur-2xl"
                    />
                    <dd
                        class="bg-gradient-to-br from-slate-900 to-slate-600 bg-clip-text text-4xl font-extrabold text-transparent tabular-nums sm:text-5xl"
                    >
                        <AnimatedCounter
                            :value="item.value"
                            :suffix="item.suffix ?? ''"
                        />
                    </dd>
                    <dt class="mt-2 text-sm font-medium text-slate-500">
                        {{ item.label }}
                    </dt>
                    <div
                        class="mx-auto mt-3 h-1 w-8 rounded-full bg-gradient-to-r from-[#2563eb] to-[#7c3aed] opacity-60"
                    />
                </div>
            </motion.div>
        </motion.dl>
    </SectionShell>
</template>
