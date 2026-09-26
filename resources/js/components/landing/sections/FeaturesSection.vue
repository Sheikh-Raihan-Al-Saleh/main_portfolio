<script setup lang="ts">
import * as icons from '@lucide/vue';
import { Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import type { Component } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { inViewOnce, perspectiveCard, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'features' }>;
    anchor?: string;
};
const props = defineProps<Props>();
const items = computed(() => props.section.data.items ?? []);
function resolveIcon(name: string | null): Component {
    if (!name) {
        return Sparkles;
    }

    const registry = icons as unknown as Record<string, Component | undefined>;

    return registry[name] ?? Sparkles;
}
const gradients = [
    'from-[#2563eb] to-[#7c3aed]',
    'from-[#f97316] to-[#fb923c]',
    'from-[#22c55e] to-[#4ade80]',
    'from-[#06b6d4] to-[#22d3ee]',
    'from-[#ec4899] to-[#f43f5e]',
    'from-[#8b5cf6] to-[#a78bfa]',
];
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
    >
        <motion.div
            :variants="stagger(0.07)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-5 [perspective:1200px] sm:grid-cols-2 lg:grid-cols-3"
        >
            <motion.div
                v-for="(item, idx) in items"
                :key="idx"
                :variants="perspectiveCard"
                class="[transform-style:preserve-3d]"
            >
                <div class="card-3d group flex h-full flex-col p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <div
                            :class="[
                                'grid size-11 place-items-center rounded-xl bg-gradient-to-br text-white shadow-md',
                                gradients[idx % gradients.length],
                            ]"
                        >
                            <component
                                :is="resolveIcon(item.icon)"
                                class="size-5"
                            />
                        </div>
                        <div
                            class="h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent opacity-0 transition-opacity group-hover:opacity-100"
                        />
                    </div>
                    <h3 class="text-base font-bold text-slate-900">
                        {{ item.title }}
                    </h3>
                    <p
                        v-if="item.text"
                        class="mt-2 text-sm leading-relaxed text-slate-500"
                    >
                        {{ item.text }}
                    </p>
                    <div
                        class="mt-4 flex items-center gap-1 text-xs font-medium text-[#2563eb] opacity-0 transition group-hover:opacity-100"
                    >
                        Learn more <span aria-hidden="true">→</span>
                    </div>
                </div>
            </motion.div>
        </motion.div>
    </SectionShell>
</template>
