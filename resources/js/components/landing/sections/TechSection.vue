<script setup lang="ts">
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import Marquee from '@/components/site/Marquee.vue';
import type { TechSectionProps } from '@/types';

/**
 * The tech stack block: a wrapped list of tags, or a scrolling ticker once
 * the list is long enough to need one.
 */

const props = defineProps<TechSectionProps>();

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
        <ul v-if="!useMarquee" class="flex flex-wrap gap-3">
            <li
                v-for="tech in items"
                :key="tech"
                class="display rounded-full border border-ink px-6 py-3 text-2xl"
            >
                {{ tech }}
            </li>
        </ul>

        <template v-else>
            <Marquee :duration="32" gap="1rem">
                <span
                    v-for="tech in items"
                    :key="tech"
                    class="display rounded-full border border-ink px-6 py-3 text-2xl whitespace-nowrap"
                >
                    {{ tech }}
                </span>
            </Marquee>
            <ul class="sr-only">
                <li v-for="tech in items" :key="tech">{{ tech }}</li>
            </ul>
        </template>
    </SectionShell>
</template>
