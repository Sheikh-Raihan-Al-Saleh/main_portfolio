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
        <div class="mx-auto max-w-3xl">
            <Accordion type="single" collapsible class="space-y-3">
                <AccordionItem
                    v-for="(item, idx) in items"
                    :key="idx"
                    :value="`faq-${idx}`"
                    class="card-3d overflow-hidden border-0 px-2"
                >
                    <AccordionTrigger
                        class="px-4 text-left text-[15px] font-semibold text-slate-900 hover:no-underline"
                    >
                        {{ item.question }}
                    </AccordionTrigger>
                    <AccordionContent
                        class="px-4 pb-4 text-sm leading-relaxed text-slate-500"
                    >
                        {{ item.answer }}
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </div>
    </SectionShell>
</template>
