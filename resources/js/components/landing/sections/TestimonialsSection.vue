<script setup lang="ts">
import { Quote } from '@lucide/vue';
import { motion } from 'motion-v';
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import SpotlightCard from '@/components/motion/SpotlightCard.vue';
import { fadeUp, inViewOnce, stagger } from '@/lib/motion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'testimonials' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const items = computed(() => props.section.data.items ?? []);

function initials(name: string): string {
    return name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('');
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
            class="grid gap-5 md:grid-cols-2 lg:grid-cols-3"
        >
            <motion.figure
                v-for="(item, index) in items"
                :key="index"
                :variants="fadeUp"
            >
                <SpotlightCard
                    class="flex h-full flex-col rounded-2xl border border-border/70 bg-card p-6"
                >
                    <Quote class="mb-4 size-7 text-brand/40" />
                    <blockquote class="text-pretty">
                        {{ item.quote }}
                    </blockquote>

                    <figcaption class="mt-6 flex items-center gap-3">
                        <img
                            v-if="item.avatar_path"
                            :src="`/media/${item.avatar_path}`"
                            :alt="item.name"
                            loading="lazy"
                            class="size-10 shrink-0 rounded-full object-cover"
                        />
                        <span
                            v-else
                            class="grid size-10 shrink-0 place-items-center rounded-full bg-brand/10 text-xs font-bold text-brand"
                            aria-hidden="true"
                        >
                            {{ initials(item.name) }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{
                                item.name
                            }}</span>
                            <span
                                v-if="item.role"
                                class="block text-xs text-muted-foreground"
                            >
                                {{ item.role }}
                            </span>
                        </span>
                    </figcaption>
                </SpotlightCard>
            </motion.figure>
        </motion.div>
    </SectionShell>
</template>
