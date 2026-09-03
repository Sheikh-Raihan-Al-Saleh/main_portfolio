<script setup lang="ts">
import { computed } from 'vue';
import SectionShell from '@/components/landing/SectionShell.vue';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import type { LandingSection } from '@/types';

type Props = {
    section: Extract<LandingSection, { type: 'faq' }>;
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
    >
        <!-- `collapsible` so an open answer can be closed again, and multiple
             so readers can compare two answers side by side. -->
        <Accordion type="multiple" collapsible class="mx-auto max-w-3xl">
            <AccordionItem
                v-for="(item, index) in items"
                :key="index"
                :value="`faq-${index}`"
            >
                <AccordionTrigger class="text-left">
                    {{ item.question }}
                </AccordionTrigger>
                <AccordionContent class="text-pretty text-muted-foreground">
                    {{ item.answer }}
                </AccordionContent>
            </AccordionItem>
        </Accordion>
    </SectionShell>
</template>
