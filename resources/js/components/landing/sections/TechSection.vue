<script setup lang="ts">
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import MarqueeRow from '@/components/motion/MarqueeRow.vue';
import TechBadge from '@/components/portfolio/TechBadge.vue';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'tech' }>;
    anchor?: string;
};

const props = defineProps<Props>();

const items = computed(() => props.section.data.items ?? []);

/** Below this count a marquee looks sparse, so render a static row instead. */
const useMarquee = computed(() => items.value.length >= 8);
</script>

<template>
    <SectionShell
        :eyebrow="section.eyebrow"
        :heading="section.heading"
        :subheading="section.subheading"
        :anchor="props.anchor"
        wide
    >
        <MarqueeRow v-if="useMarquee" :duration="45">
            <TechBadge
                v-for="item in items"
                :key="item"
                :label="item"
                class="text-sm"
            />
        </MarqueeRow>

        <div v-else class="flex flex-wrap justify-center gap-2">
            <TechBadge v-for="item in items" :key="item" :label="item" />
        </div>
    </SectionShell>
</template>
