<script setup lang="ts">
import * as icons from '@lucide/vue';
import { Sparkles } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import type { Component } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
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
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
    >
        <motion.div
            :variants="stagger(0.08)"
            initial="hidden"
            while-in-view="visible"
            :in-view-options="inViewOnce"
            class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3"
        >
            <motion.div
                v-for="(item, index) in items"
                :key="index"
                :variants="fadeUp"
            >
                <div
                    class="flex h-full flex-col rounded-lg border border-border p-6 transition-shadow duration-300 hover:shadow-lg"
                >
                    <div
                        class="mb-4 grid size-11 place-items-center rounded-lg bg-muted text-muted-foreground"
                    >
                        <component
                            :is="resolveIcon(item.icon)"
                            class="size-5"
                        />
                    </div>
                    <h3 class="mb-2 font-display font-semibold">
                        {{ item.title }}
                    </h3>
                    <p
                        v-if="item.text"
                        class="text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ item.text }}
                    </p>
                </div>
            </motion.div>
        </motion.div>
    </SectionShell>
</template>
